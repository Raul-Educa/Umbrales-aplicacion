<?php

namespace App\Http\Controllers;

use App\Services\AlertasUmbralesService;
use Illuminate\Http\Request;

class EmbalsesController extends Controller
{
    protected $alertasService;

    public function __construct(AlertasUmbralesService $alertasService)
    {
        $this->alertasService = $alertasService;
    }

    public function verCCAA($id)
    {
        $embalses = $this->alertasService->DetalleAlertasPorCCAA($id);


        $nombresCCAA = [
            2 => 'Aragón',
            8 => 'Castilla La Mancha',
            11 => 'Extremadura',
            13 => 'Madrid',
            7 => 'Castilla y León',
            99 => 'Portugal',
        ];

        $titulo = $nombresCCAA[$id] ?? "Comunidad Autónoma $id";

        return view('auth.embalses_detalle', [
            'embalses' => $embalses,
            'titulo' => $titulo
        ]);
    }
    public function buscar(Request $request)
{
    $query = $request->input('q');

    if (empty($query)) {
        return redirect()->back();
    }

    $embalses = $this->alertasService->BuscarEmbalses($query);

    return view('auth.embalses_detalle', [
        'embalses' => $embalses,
        'titulo' => "Resultados para: '$query'"
    ]);
}
}
