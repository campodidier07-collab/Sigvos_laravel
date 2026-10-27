<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CosechaController extends Controller
{
    /**
     * Listado de cosechas registradas y cultivos listos para ser cosechados.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        // 1. Cosechas ya realizadas
        $queryCosechas = Cultivo::with(['lote', 'variedad.tipoCultivo', 'registradoPor'])
            ->where('estado', 'cosechado')
            ->orderByDesc('fecha_cosecha_real');

        if ($usuario->esTrabajador()) {
            $queryCosechas->whereHas('lote.trabajadores', fn ($q) => $q->where('id_usuario', $usuario->id));
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $queryCosechas->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhereHas('variedad', fn ($v) => $v->where('nombre', 'like', "%{$busqueda}%"))
                  ->orWhereHas('lote', fn ($l) => $l->where('nombre', 'like', "%{$busqueda}%"));
            });
        }

        $totalKilos = (clone $queryCosechas)->sum('cantidad_cosechada_kg');
        $cosechas = $queryCosechas->paginate(15)->through(function ($c) {
            return [
                'id'                    => $c->id,
                'codigo'                => $c->codigo,
                'variedad'              => $c->variedad?->nombre ?? 'Variedad',
                'tipo_cultivo'          => $c->variedad?->tipoCultivo?->nombre ?? 'General',
                'lote_nombre'           => $c->lote?->nombre ?? 'Sin lote',
                'lote_area_ha'          => (float) ($c->lote?->area_ha ?? 0),
                'fecha_siembra'         => $c->fecha_siembra?->format('Y-m-d'),
                'fecha_cosecha_real'    => $c->fecha_cosecha_real?->format('Y-m-d'),
                'cantidad_cosechada_kg' => (float) ($c->cantidad_cosechada_kg ?? 0),
                'observaciones'         => $c->observaciones,
                'fotografia'            => $c->fotografia ? asset('storage/' . $c->fotografia) : null,
            ];
        });

        // 2. Cultivos activos listos para cosechar
        $queryListos = Cultivo::with(['lote', 'variedad'])
            ->whereNotNull('activo_en_lote')
            ->where('estado', 'sembrado');

        if ($usuario->esTrabajador()) {
            $queryListos->whereHas('lote.trabajadores', fn ($q) => $q->where('id_usuario', $usuario->id));
        }

        $cultivosListos = $queryListos->orderBy('fecha_cosecha_estimada')->get()->map(function ($c) {
            return [
                'id'                     => $c->id,
                'codigo'                 => $c->codigo,
                'variedad'               => $c->variedad?->nombre ?? 'Variedad',
                'lote_nombre'            => $c->lote?->nombre ?? 'Lote',
                'fecha_cosecha_estimada' => $c->fecha_cosecha_estimada?->format('Y-m-d'),
            ];
        });

        return response()->json([
            'resumen' => [
                'total_kg_cosechados'  => (float) $totalKilos,
                'total_cosechas'       => $cosechas->total(),
                'cultivos_pendientes'  => $cultivosListos->count(),
            ],
            'cultivos_listos' => $cultivosListos,
            'cosechas'        => $cosechas,
        ]);
    }

    /**
     * Registrar la cosecha de un cultivo.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_cultivo'            => ['required', 'exists:cultivos,id'],
            'fecha_cosecha_real'    => ['required', 'date'],
            'cantidad_cosechada_kg' => ['required', 'numeric', 'min:0.1'],
            'estado_siguiente_lote' => ['nullable', 'string', Rule::in(['disponible', 'en_descanso'])],
            'observaciones'         => ['nullable', 'string', 'max:1000'],
        ]);

        $cultivo = Cultivo::with('lote')->findOrFail($validated['id_cultivo']);

        $usuario = $request->user();
        if ($usuario->esTrabajador() && ! $cultivo->lote?->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            return response()->json(['message' => 'No tienes permisos sobre este cultivo.'], 403);
        }

        DB::transaction(function () use ($cultivo, $validated) {
            $cultivo->update([
                'estado'                => 'cosechado',
                'fecha_cosecha_real'    => $validated['fecha_cosecha_real'],
                'cantidad_cosechada_kg' => $validated['cantidad_cosechada_kg'],
                'observaciones'         => $validated['observaciones'] ?? $cultivo->observaciones,
                'activo_en_lote'        => null,
            ]);

            // Liberar lote
            $estadoLote = $validated['estado_siguiente_lote'] ?? 'disponible';
            $cultivo->lote?->update(['estado' => $estadoLote]);
        });

        return response()->json([
            'message' => 'Cosecha registrada exitosamente. El lote ha quedado disponible.',
        ]);
    }
}
