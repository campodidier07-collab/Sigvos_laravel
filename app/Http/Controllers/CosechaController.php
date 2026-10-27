<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
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

        // Metricas generales de cosecha
        $totalKgCosechados = Cultivo::where('estado', 'cosechado')->sum('cantidad_cosechada_kg') ?? 0;
        $totalCosechados = Cultivo::where('estado', 'cosechado')->count();
        $totalPendientes = Cultivo::whereIn('estado', ['sembrado', 'creciendo', 'maduro'])->count();
        $totalLotesLiberados = Cultivo::where('estado', 'cosechado')->whereNotNull('id_lote')->distinct('id_lote')->count('id_lote');

        return view('cosecha.index', compact(
            'cultivos',
            'cultivosParaCosechar',
            'totalKgCosechados',
            'totalCosechados',
            'totalPendientes',
            'totalLotesLiberados'
        ));
    }

    /**
     * HU DP-17: Registrar cosecha, liberar lote y comparar con estimación inicial.
     * Integración con DP-09: Cancelación automática de actividades huérfanas al cerrar el cultivo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_cultivo'            => 'required|exists:cultivos,id',
            'fecha_cosecha_real'    => 'required|date|before_or_equal:today',
            'cantidad_cosechada_kg' => 'required|numeric|min:0.1',
            'observaciones'         => 'nullable|string|max:1000'
        ], [
            'cantidad_cosechada_kg.min'          => 'La cantidad cosechada debe ser un valor numérico positivo mayor a 0 kg.',
            'fecha_cosecha_real.before_or_equal' => 'La fecha de cosecha no puede ser una fecha futura.',
        ]);

        $cultivo = Cultivo::with('lote')->findOrFail($request->id_cultivo);
        
        $cultivo->update([
            'estado'                => 'cosechado',
            'fecha_cosecha_real'    => $request->fecha_cosecha_real,
            'cantidad_cosechada_kg' => $request->cantidad_cosechada_kg,
            'observaciones'         => $request->observaciones,
            'activo_en_lote'        => null,
        ]);

        // Criterio 1 y 2: Liberar automáticamente el lote y marcarlo como disponible
        if ($cultivo->lote) {
            $cultivo->lote->update(['estado' => 'disponible']);
        }

        // Hardening / Gap DP-17 + DP-09: Auto-cierre de actividades pendientes huérfanas
        $actividadesPendientes = Actividad::where('id_cultivo', $cultivo->id)
            ->where('estado', 'pendiente')
            ->get();

        $totalCanceladas = 0;
        foreach ($actividadesPendientes as $act) {
            $nota = trim(($act->observaciones ?? '') . ' [Cancelada automáticamente por finalización de cosecha]');
            $act->update([
                'estado'        => 'cancelada',
                'observaciones' => $nota,
            ]);
            $totalCanceladas++;
        }

        // Criterio 6: Comparación con estimación inicial
        $mensajeExito = "¡Cosecha registrada exitosamente! El cultivo se ha cerrado y el lote {$cultivo->lote->identificador} ha quedado disponible.";
        if ($totalCanceladas > 0) {
            $mensajeExito .= " Se cancelaron automáticamente {$totalCanceladas} tarea(s) pendiente(s) asociada(s).";
        }
        if (!empty($cultivo->cantidad_estimada_kg)) {
            $diferencia = round($request->cantidad_cosechada_kg - $cultivo->cantidad_estimada_kg, 2);
            $signo = $diferencia >= 0 ? "+{$diferencia}" : "{$diferencia}";
            $mensajeExito .= " Comparación con estimación: {$signo} kg respecto a lo proyectado ({$cultivo->cantidad_estimada_kg} kg).";
        }

        return redirect()->route('cosecha.index')->with('success', $mensajeExito);
    }
}
