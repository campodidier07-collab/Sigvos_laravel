<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use Illuminate\Http\Request;

class CosechaController extends Controller
{
    public function index(Request $request)
    {
        // Mostrar cultivos que ya están cosechados o que están próximos a cosechar
        // (por ejemplo, aquellos en estado 'creciendo' o 'cosechado')
        $query = Cultivo::with(['lote', 'variedad'])
            ->whereIn('estado', ['creciendo', 'cosechado'])
            ->orderBy('fecha_cosecha_estimada', 'asc');

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhereHas('lote', function($l) use ($busqueda) {
                      $l->where('nombre', 'like', "%{$busqueda}%");
                  });
            });
        }

        $cultivos = $query->paginate(15);
        
        // Cultivos listos para cosechar (para el modal/formulario)
        $cultivosParaCosechar = Cultivo::with('lote', 'variedad')
            ->whereIn('estado', ['sembrado', 'creciendo', 'maduro'])
            ->get();

        return view('cosecha.index', compact('cultivos', 'cultivosParaCosechar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cultivo' => 'required|exists:cultivos,id',
            'fecha_cosecha_real' => 'required|date',
            'cantidad_cosechada_kg' => 'required|numeric|min:0.1',
            'observaciones' => 'nullable|string'
        ]);

        $cultivo = Cultivo::findOrFail($request->id_cultivo);
        
        $cultivo->update([
            'estado' => 'cosechado',
            'fecha_cosecha_real' => $request->fecha_cosecha_real,
            'cantidad_cosechada_kg' => $request->cantidad_cosechada_kg,
            'observaciones' => $request->observaciones,
            'activo_en_lote' => false
        ]);

        return redirect()->route('cosecha.index')->with('success', '¡Cosecha registrada exitosamente! El cultivo se ha cerrado.');
    }
}
