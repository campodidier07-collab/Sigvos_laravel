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

        return view('cosecha.index', compact('cultivos'));
    }
}
