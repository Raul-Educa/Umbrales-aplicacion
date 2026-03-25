<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SituacionEmergencia;
use App\Models\UmbralesCcaa;
use App\Models\UmbralesProvincia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EmergenciaController extends Controller
{
    public function crear()
    {
        $ccaaParaFormulario = \App\Models\UmbralesCcaa::where('c_comunidad_autonoma', 'NOT ILIKE', '%Arag%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%Portugal%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%AY%')
            ->get();

        $provinciasParaFormulario = \App\Models\UmbralesProvincia::all();

        return view('auth.situacion_formulario', compact('ccaaParaFormulario', 'provinciasParaFormulario'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'ccaa_id' => 'required|exists:umbrales_ccaa,c_id',
            'nivel' => 'required|integer|min:0|max:5',
            'fecha' => 'required|date|before_or_equal:today',
            'hora' => 'required',
            'tipo_documento' => 'required|in:pdf_oficial,texto_correo',
            'texto_correo' => 'required_if:tipo_documento,texto_correo',
            'archivo_pdf' => 'required_if:tipo_documento,pdf_oficial'
        ], [
            'fecha.before_or_equal' => 'Error: La fecha de la emergencia no puede ser futura',
            'texto_correo.required_if' => 'Error: Debes pegar el contenido del correo para generar el PDF.',
            'archivo_pdf.required_if' => 'Error: Debes adjuntar un archivo PDF oficial.'
        ]);

        $hoy = \Carbon\Carbon::now()->format('Y-m-d');
        $horaActual = \Carbon\Carbon::now()->format('H:i');

        if ($request->fecha == $hoy && $request->hora > $horaActual) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['hora' => 'Error: La hora de la emergencia no puede ser futura']);
        }

        // =====================================================================
        // 1. CALCULAMOS LAS PROVINCIAS AFECTADAS ANTES DE HACER EL PDF
        // =====================================================================
        $provinciasIds = $request->input('provincias_ids', []);
        $tieneProvincias = UmbralesProvincia::where('c_id', $request->ccaa_id)->exists();

        if ($tieneProvincias && empty($provinciasIds)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['provincias_ids' => 'Debe marcar al menos una provincia o seleccionar todas.']);
        }

        $textoProvincias = "";

        if (!$tieneProvincias) {
            $provinciasIds = [null];
            $textoProvincias = "Toda la Comunidad Autónoma"; // Caso Madrid, que no tiene provincias
        } else {
            $provinciasDeLaCcaa = UmbralesProvincia::where('c_id', $request->ccaa_id)->get();

            // Si el número de provincias marcadas es igual al total de provincias de esa CCAA...
            if (count($provinciasIds) == $provinciasDeLaCcaa->count()) {
                $textoProvincias = "Todas las provincias (Afectación a nivel autonómico)";
            } else {
                // Si solo son algunas, sacamos sus nombres y los separamos por comas
                $nombresProvincias = $provinciasDeLaCcaa->whereIn('p_id', $provinciasIds)->pluck('p_provincia')->toArray();
                $textoProvincias = implode(', ', $nombresProvincias);
            }
        }

        // =====================================================================
        // 2. GESTIÓN DEL PDF (Ahora le pasamos la variable 'provincias')
        // =====================================================================
        $rutaPdf = null;

        if ($request->tipo_documento == 'pdf_oficial' && $request->hasFile('archivo_pdf')) {
            $archivo = $request->file('archivo_pdf');
            $nombrePdf = time() . '_' . $archivo->getClientOriginalName();
            $rutaPdf = $archivo->storeAs('pdf_emergencia', $nombrePdf, 'public');
        }

        if ($request->tipo_documento == 'texto_correo' && $request->filled('texto_correo')) {
            $pdfCorreo = Pdf::loadView('auth.plantilla_pdf', [
                'texto'      => $request->texto_correo,
                'fecha'      => $request->fecha,
                'hora'       => $request->hora,
                'ccaa'       => UmbralesCcaa::find($request->ccaa_id)->c_comunidad_autonoma,
                'provincias' => $textoProvincias // <-- ¡LA NUEVA VARIABLE!
            ]);

            $nombrePdf = time() . '_generado_correo.pdf';
            Storage::disk('public')->put('pdf_emergencia/' . $nombrePdf, $pdfCorreo->output());
            $rutaPdf = 'pdf_emergencia/' . $nombrePdf;
        }

        // =====================================================================
        // 3. GUARDAMOS EN LA BASE DE DATOS
        // =====================================================================
        DB::transaction(function () use ($request, $rutaPdf, $provinciasIds) {
            foreach ($provinciasIds as $provId) {
                SituacionEmergencia::create([
                    'ccaa_id'      => $request->ccaa_id,
                    'provincia_id' => $provId,
                    'nivel'        => $request->nivel,
                    'fecha'        => $request->fecha,
                    'hora'         => $request->hora,
                    'descripcion'  => $request->descripcion,
                    'ruta_pdf'     => $rutaPdf,
                    'usuario_id'   => session('id') ?? 1
                ]);
            }
        });

        return redirect()->back()->with('success', 'Situación de emergencia registrada correctamente');
    }

    public function vistaPlanEmergencia()
    {
        $ahora = \Carbon\Carbon::now();
        $meses = [$ahora->copy()->subMonth()->startOfMonth(), $ahora->copy()->startOfMonth()];
        $hoy = $ahora->format('Y-m-d');

        $ccaa = \App\Models\UmbralesCcaa::with('provincias')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%Arag%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%Portugal%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%AY%')
            ->get();

        $registros = \App\Models\SituacionEmergencia::orderBy('fecha', 'asc')->orderBy('hora', 'asc')->get();

        $nombresNiveles = [
            0 => 'Normalidad',
            1 => 'Preemergencia',
            2 => 'Situación 0',
            3 => 'Situación 1',
            4 => 'Situación 2',
            5 => 'Situación 3'
        ];

        $matrizColores = [];

        foreach ($ccaa as $comunidad) {
            $registrosCcaa = $registros->filter(fn($registro) => $registro->ccaa_id == $comunidad->c_id);

            foreach ($meses as $mes) {
                for ($dia = 1; $dia <= $mes->daysInMonth; $dia++) {
                    $fecha = $mes->copy()->day($dia)->format('Y-m-d');

                    if ($fecha > $hoy) continue;

                    $zonas = [null];
                    foreach ($comunidad->provincias as $provincia) {
                        $zonas[] = $provincia->p_id;
                    }

                    foreach ($zonas as $zonaId) {

                        $historialPrevio = $registrosCcaa->filter(function ($registro) use ($fecha, $zonaId) {
                            $fechaRegistro = substr((string)$registro->fecha, 0, 10);
                            return $registro->provincia_id == $zonaId && $fechaRegistro < $fecha;
                        })->sortByDesc('fecha')->sortByDesc('hora');

                        $estadoAnterior = $historialPrevio->first();

                        $eventosHoy = $registrosCcaa->filter(function ($registro) use ($fecha, $zonaId) {
                            $fechaRegistro = substr((string)$registro->fecha, 0, 10);
                            return $registro->provincia_id == $zonaId && $fechaRegistro == $fecha;
                        })->sortBy('hora');

                        $ultimoEstado = $eventosHoy->last() ?? $estadoAnterior;

                        if ($ultimoEstado && $ultimoEstado->nivel > 0) {
                            $huboCambio = false;

                            if ($eventosHoy->count() > 0) {
                                $huboCambio = ($estadoAnterior && $estadoAnterior->nivel > 0) || ($eventosHoy->count() > 1);
                                $historialDia = "<b>HISTORIAL DEL DÍA:</b><br><br>";

                                if ($estadoAnterior && $estadoAnterior->nivel > 0) {
                                    $nombreNivel = $nombresNiveles[$estadoAnterior->nivel] ?? '';

                                    $textoDesc = strpos($estadoAnterior->descripcion, ':') !== false
                                        ? trim(explode(':', $estadoAnterior->descripcion, 2)[1])
                                        : $estadoAnterior->descripcion;

                                    $fechaOriginal = \Carbon\Carbon::parse($estadoAnterior->fecha)->format('d/m/Y');
                                    $horaOriginal = \Carbon\Carbon::parse($estadoAnterior->hora)->format('H:i');

                                    $historialDia .= "<b>" . $nombreNivel . "</b> <span><i>Inicio: " . $fechaOriginal . " (" . $horaOriginal . ")</i></span><br>" . $textoDesc . "<br>";

                                    if ($estadoAnterior->ruta_pdf) {
                                        $historialDia .= "<a href='/storage/" . $estadoAnterior->ruta_pdf . "' target='_blank' style='display:inline-block; margin-top:6px; padding:6px 12px; border:1px solid #d9534f; border-radius:5px; background-color:#fef2f2; color:#d9534f; font-size:0.85em; font-weight:bold; text-decoration:none;'>Ver / Descargar PDF {$fechaOriginal}</a><br>";
                                    }
                                    $historialDia .= "<hr style='border-top:1px dashed #ccc; margin: 10px 0;'>";
                                }

                                foreach ($eventosHoy as $evento) {
                                    $nombreNivel = $nombresNiveles[$evento->nivel] ?? '';

                                    $textoDesc = strpos($evento->descripcion, ':') !== false
                                        ? trim(explode(':', $evento->descripcion, 2)[1])
                                        : $evento->descripcion;

                                    $historialDia .= "<b>" . \Carbon\Carbon::parse($evento->hora)->format('H:i') . "h - " . $nombreNivel . "</b><br>" . $textoDesc . "<br>";

                                    if ($evento->ruta_pdf) {
                                        $historialDia .= "<a href='/storage/" . $evento->ruta_pdf . "' target='_blank' style='display:inline-block; margin-top:6px; padding:6px 12px; border:1px solid #d9534f; border-radius:5px; background-color:#fef2f2; color:#d9534f; font-size:0.85em; font-weight:bold; text-decoration:none;'>Ver / Descargar PDF</a><br>";
                                    }
                                    $historialDia .= "<br>";
                                }
                            } else {
                                $fechaOriginal = \Carbon\Carbon::parse($estadoAnterior->fecha)->format('d/m/Y');
                                $horaOriginal = \Carbon\Carbon::parse($estadoAnterior->hora)->format('H:i');

                                $textoDesc = strpos($estadoAnterior->descripcion, ':') !== false
                                    ? trim(explode(':', $estadoAnterior->descripcion, 2)[1])
                                    : $estadoAnterior->descripcion;

                                $historialDia = "<span>(Situación iniciada el " . $fechaOriginal . " a las " . $horaOriginal . "h)</span><br><br>" . $textoDesc;
                            }

                            $claveZona = $zonaId === null ? 'global' : $zonaId;

                            $matrizColores[$comunidad->c_id][$claveZona][$fecha] = [
                                'nivel'  => $ultimoEstado->nivel,
                                'hora'   => $ultimoEstado->hora,
                                'desc'   => $historialDia,
                                'pdf'    => $ultimoEstado->ruta_pdf,
                                'cambio' => $huboCambio
                            ];
                        }
                    }
                }
            }
        }

        return view('auth.vista_PlanEmergencia', compact('ccaa', 'meses', 'matrizColores', 'hoy'));
    }
}
