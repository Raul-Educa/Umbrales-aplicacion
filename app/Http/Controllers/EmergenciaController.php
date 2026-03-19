<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SituacionEmergencia;
use App\Models\UmbralesCcaa;
use App\Models\UmbralesProvincia; // Importante para la comprobación
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Importante para la transacción

class EmergenciaController extends Controller
{
    public function crear()
    {
        $ccaaParaFormulario = \App\Models\UmbralesCcaa::all();
        $provinciasParaFormulario = \App\Models\UmbralesProvincia::all();
        return view('auth.situacion_formulario', compact('ccaaParaFormulario', 'provinciasParaFormulario'));
    }

    public function guardar(Request $request)
    {
        // 1. VALIDACIÓN (Actualizado max:5 para los nuevos colores)
        $request->validate([
            'ccaa_id' => 'required|exists:umbrales_ccaa,c_id',
            'nivel' => 'required|integer|min:0|max:5', // <--- CAMBIADO A 5
            'fecha' => 'required|date',
            'hora' => 'required',
            'tipo_documento' => 'required|in:pdf_oficial,texto_correo'
        ]);

        // 2. GESTIÓN DEL ARCHIVO
        $rutaPdfFinal = null;

        if ($request->tipo_documento == 'pdf_oficial' && $request->hasFile('archivo_pdf')) {
            $archivo = $request->file('archivo_pdf');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaPdfFinal = $archivo->storeAs('pdf_emergencia', $nombreArchivo, 'public');
        }

        if ($request->tipo_documento == 'texto_correo' && $request->filled('texto_correo')) {
            // <--- CAMBIADO A 'auth.plantilla_pdf'
            $pdf = Pdf::loadView('auth.plantilla_pdf', [
                'texto' => $request->texto_correo,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'ccaa' => UmbralesCcaa::find($request->ccaa_id)->c_comunidad_autonoma
            ]);

            $nombreArchivo = time() . '_generado_correo.pdf';
            Storage::disk('public')->put('pdf_emergencia/' . $nombreArchivo, $pdf->output());
            $rutaPdfFinal = 'pdf_emergencia/' . $nombreArchivo;
        }

        // 3. CAPTURAR PROVINCIAS Y EVITAR "FALSO GLOBAL"
        $provinciasSeleccionadas = $request->input('provincias_ids', []);

        $ccaaTieneProvincias = UmbralesProvincia::where('c_id', $request->ccaa_id)->exists();

        if ($ccaaTieneProvincias && empty($provinciasSeleccionadas)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['provincias_ids' => 'Debe marcar al menos una provincia o seleccionar todas.']);
        }

        if (!$ccaaTieneProvincias) {
            $provinciasSeleccionadas = [null];
        }

        // 4. GUARDAR EN BASE DE DATOS
        DB::transaction(function () use ($request, $rutaPdfFinal, $provinciasSeleccionadas) {
            foreach ($provinciasSeleccionadas as $provId) {
                SituacionEmergencia::create([
                    'ccaa_id'      => $request->ccaa_id,
                    'provincia_id' => $provId,
                    'nivel'        => $request->nivel,
                    'fecha'        => $request->fecha,
                    'hora'         => $request->hora,
                    'descripcion'  => $request->descripcion,
                    'ruta_pdf'     => $rutaPdfFinal,

                    // <--- USUARIO SEGURO: Coge el ID real, y si caduca la sesión, usa el 1 para no fallar
                    'usuario_id'   => session('id') ?? 1
                ]);
            }
        });

        return redirect()->back()->with('success', 'Situación registrada correctamente en las zonas afectadas.');
    }

    public function vistaPlanEmergencia()
    {
        $ahora = \Carbon\Carbon::now();
        $meses = [$ahora->copy()->subMonth()->startOfMonth(), $ahora->copy()->startOfMonth()];
        $hoy = $ahora->format('Y-m-d');

        $ccaa = \App\Models\UmbralesCcaa::with('provincias')->get();

        $todosLosRegistros = \App\Models\SituacionEmergencia::orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        // Nombres limpios que me pediste
        $nombresNiveles = [
            0 => 'Normalidad',
            1 => 'Preemergencia',
            2 => 'Situación 0',
            3 => 'Situación 1',
            4 => 'Situación 2',
            5 => 'Situación 3'
        ];

        $matrizColores = [];
        foreach ($ccaa as $c) {
            $regsCcaa = $todosLosRegistros->where('ccaa_id', $c->c_id);

            foreach ($meses as $m) {
                for ($d = 1; $d <= $m->daysInMonth; $d++) {
                    $fecha = $m->copy()->day($d)->format('Y-m-d');
                    if ($fecha > $hoy) continue;

                    // --- LÓGICA GLOBAL ---
                    $historialGlobal = $regsCcaa->where('provincia_id', null)->where('fecha', '<=', $fecha)->sortByDesc('fecha')->sortByDesc('hora');
                    $ultimoGlobal = $historialGlobal->first();

                    if ($ultimoGlobal && $ultimoGlobal->nivel > 0) {
                        $eventosHoy = $regsCcaa->where('provincia_id', null)->where('fecha', $fecha)->sortBy('hora');

                        $descFinal = $ultimoGlobal->descripcion;
                        if ($eventosHoy->count() > 1) {
                            $descFinal = "<b>HISTORIAL DEL DÍA:</b><br><br>";
                            foreach($eventosHoy as $ev) {
                                $nombreSit = $nombresNiveles[$ev->nivel] ?? '';

                                // ELIMINADOR DE COLORES Y ALERTAS (Borra todo hasta los primeros dos puntos)
                                $textoLimpio = $ev->descripcion;
                                if (strpos($textoLimpio, ':') !== false) {
                                    $textoLimpio = trim(explode(':', $textoLimpio, 2)[1]);
                                }

                                $descFinal .= "<b>" . \Carbon\Carbon::parse($ev->hora)->format('H:i') . "h - " . $nombreSit . "</b><br>" . $textoLimpio . "<br><br>";
                            }
                        }

                        $matrizColores[$c->c_id]['global'][$fecha] = [
                            'nivel' => $ultimoGlobal->nivel,
                            'hora'  => $ultimoGlobal->hora,
                            'desc'  => $descFinal,
                            'pdf'   => $ultimoGlobal->ruta_pdf
                        ];
                    }

                    // --- LÓGICA PROVINCIAS ---
                    foreach ($c->provincias as $p) {
                        $historialProv = $regsCcaa->where('provincia_id', $p->p_id)->where('fecha', '<=', $fecha)->sortByDesc('fecha')->sortByDesc('hora');
                        $ultimoProv = $historialProv->first();

                        if ($ultimoProv && $ultimoProv->nivel > 0) {
                            $eventosHoy = $regsCcaa->where('provincia_id', $p->p_id)->where('fecha', $fecha)->sortBy('hora');

                            $descFinal = $ultimoProv->descripcion;
                            if ($eventosHoy->count() > 1) {
                                $descFinal = "<b>HISTORIAL DEL DÍA:</b><br><br>";
                                foreach($eventosHoy as $ev) {
                                    $nombreSit = $nombresNiveles[$ev->nivel] ?? '';

                                    // ELIMINADOR DE COLORES Y ALERTAS
                                    $textoLimpio = $ev->descripcion;
                                    if (strpos($textoLimpio, ':') !== false) {
                                        $textoLimpio = trim(explode(':', $textoLimpio, 2)[1]);
                                    }

                                    $descFinal .= "<b>" . \Carbon\Carbon::parse($ev->hora)->format('H:i') . "h - " . $nombreSit . "</b><br>" . $textoLimpio . "<br><br>";
                                }
                            }

                            $matrizColores[$c->c_id][$p->p_id][$fecha] = [
                                'nivel' => $ultimoProv->nivel,
                                'hora'  => $ultimoProv->hora,
                                'desc'  => $descFinal,
                                'pdf'   => $ultimoProv->ruta_pdf
                            ];
                        }
                    }
                }
            }
        }

        return view('auth.vista_PlanEmergencia', compact('ccaa', 'meses', 'matrizColores', 'hoy'));
    }
}
