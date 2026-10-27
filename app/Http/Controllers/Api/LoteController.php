<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoteResource;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LoteController extends Controller
{
    /**
     * Listar lotes con filtros, búsqueda y estadísticas por estado.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $query = Lote::with(['tipoPreferido', 'cultivoActivo.variedad.tipoCultivo']);

        // Si es trabajador, solo ve los lotes que tiene asignados
        if ($usuario->esTrabajador()) {
            $query->whereHas('trabajadores', function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por identificador, nombre o ubicación
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('identificador', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('ubicacion', 'like', "%{$busqueda}%");
            });
        }

        // Calcular conteos por estado
        $statsQuery = clone $query;
        $conteoPorEstado = [
            'total'       => (clone $query)->count(),
            'disponible'  => (clone $statsQuery)->where('estado', 'disponible')->count(),
            'ocupado'     => (clone $statsQuery)->where('estado', 'ocupado')->count(),
            'en_descanso' => (clone $statsQuery)->where('estado', 'en_descanso')->count(),
            'inactivo'    => (clone $statsQuery)->where('estado', 'inactivo')->count(),
        ];

        $perPage = (int) $request->input('per_page', 15);
        $lotes = $query->orderBy('identificador')->paginate($perPage);

        return response()->json([
            'conteo_estados' => $conteoPorEstado,
            'data'           => LoteResource::collection($lotes)->response()->getData(true),
        ]);
    }

    /**
     * Crear un nuevo lote (Solo administradores).
     */
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permisos para crear lotes.'], 403);
        }

        if ($request->filled('identificador')) {
            $request->merge(['identificador' => strtoupper(trim($request->input('identificador')))]);
        }

        $validated = $request->validate([
            'identificador'     => ['required', 'string', 'size:1', 'unique:lotes,identificador'],
            'nombre'            => ['required', 'string', 'max:100'],
            'ubicacion'         => ['required', 'string', 'max:200'],
            'area_ha'           => ['required', 'numeric', 'min:0.01'],
            'id_tipo_preferido' => ['nullable', 'exists:tipos_cultivo,id'],
            'es_alternativo'    => ['nullable', 'boolean'],
            'fotografia'        => ['nullable', 'image', 'max:10240'],
        ]);

        $validated['es_alternativo'] = (bool) ($validated['es_alternativo'] ?? false);
        $validated['estado'] = 'disponible';
        $validated['activo'] = true;

        if ($request->hasFile('fotografia')) {
            $validated['fotografia'] = $request->file('fotografia')->store('lotes', 'public');
        }

        $lote = Lote::create($validated);
        $lote->load('tipoPreferido');

        return response()->json([
            'message' => 'Lote creado exitosamente.',
            'data'    => new LoteResource($lote),
        ], 201);
    }

    /**
     * Ver detalle completo de un lote.
     */
    public function show(Request $request, Lote $lote): JsonResponse
    {
        $usuario = $request->user();

        // Validar acceso del trabajador
        if ($usuario->esTrabajador() && ! $lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            return response()->json(['message' => 'No tienes asignado este lote.'], 403);
        }

        $lote->load([
            'tipoPreferido',
            'cultivoActivo.variedad.tipoCultivo',
            'trabajadores',
            'cultivos' => fn ($q) => $q->orderByDesc('fecha_siembra')->limit(5),
        ]);

        return response()->json([
            'data' => new LoteResource($lote),
        ]);
    }

    /**
     * Actualizar información de un lote (Solo administradores).
     */
    public function update(Request $request, Lote $lote): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permisos para editar lotes.'], 403);
        }

        $validated = $request->validate([
            'identificador'     => ['required', 'string', 'max:10', Rule::unique('lotes', 'identificador')->ignore($lote->id)],
            'nombre'            => ['required', 'string', 'max:100'],
            'ubicacion'         => ['required', 'string', 'max:200'],
            'area_ha'           => ['required', 'numeric', 'min:0.01'],
            'id_tipo_preferido' => ['nullable', 'exists:tipos_cultivo,id'],
            'estado'            => ['nullable', 'string', Rule::in(['disponible', 'ocupado', 'en_descanso', 'inactivo'])],
            'es_alternativo'    => ['nullable', 'boolean'],
            'activo'            => ['nullable', 'boolean'],
            'fotografia'        => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('fotografia')) {
            if ($lote->fotografia && Storage::disk('public')->exists($lote->fotografia)) {
                Storage::disk('public')->delete($lote->fotografia);
            }
            $validated['fotografia'] = $request->file('fotografia')->store('lotes', 'public');
        }

        $lote->update($validated);
        $lote->load(['tipoPreferido', 'cultivoActivo.variedad.tipoCultivo']);

        return response()->json([
            'message' => 'Lote actualizado correctamente.',
            'data'    => new LoteResource($lote),
        ]);
    }

    /**
     * Desactivar o eliminar lote.
     */
    public function destroy(Request $request, Lote $lote): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permisos para eliminar lotes.'], 403);
        }

        if ($lote->cultivoActivo()->exists()) {
            return response()->json([
                'message' => 'No puedes eliminar un lote que tiene un cultivo activo actualmente en curso.',
            ], 422);
        }

        $lote->update(['activo' => false, 'estado' => 'inactivo']);

        return response()->json([
            'message' => 'Lote desactivado correctamente.',
        ]);
    }
}
