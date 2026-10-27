<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CultivoResource;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Variedad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CultivoController extends Controller
{
    /**
     * Listar cultivos con relaciones, búsqueda y filtros de estado.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $query = Cultivo::with(['lote', 'variedad.tipoCultivo', 'registradoPor'])
            ->withCount(['actividades', 'fotos']);

        // Si es trabajador, solo ve los cultivos de sus lotes asignados
        if ($usuario->esTrabajador()) {
            $query->whereHas('lote.trabajadores', function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        // Filtro por lote
        if ($request->filled('id_lote')) {
            $query->where('id_lote', $request->id_lote);
        }

        // Búsqueda por código o nombre de variedad
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhereHas('variedad', function ($v) use ($busqueda) {
                      $v->where('nombre', 'like', "%{$busqueda}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $cultivos = $query->orderByDesc('fecha_siembra')->paginate($perPage);

        return response()->json([
            'data' => CultivoResource::collection($cultivos)->response()->getData(true),
        ]);
    }

    /**
     * Registrar una nueva siembra de cultivo.
     */
    public function store(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'id_lote'                => ['required', 'exists:lotes,id'],
            'id_variedad'            => ['required', 'exists:variedades,id'],
            'codigo'                 => ['nullable', 'string', 'max:50', 'unique:cultivos,codigo'],
            'fecha_siembra'          => ['required', 'date'],
            'fecha_cosecha_estimada' => ['nullable', 'date', 'after_or_equal:fecha_siembra'],
            'observaciones'          => ['nullable', 'string', 'max:1000'],
            'fotografia'             => ['nullable', 'image', 'max:10240'],
        ]);

        $lote = Lote::findOrFail($validated['id_lote']);

        // Si es trabajador, verificar asignación
        if ($usuario->esTrabajador() && ! $lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            return response()->json(['message' => 'No tienes asignado este lote para sembrar.'], 403);
        }

        // Criterio 4: Verificar si el lote está disponible
        if ($lote->estado !== 'disponible' || $lote->cultivoActivo()->exists()) {
            return response()->json([
                'message' => 'El lote seleccionado no está disponible. Solo se pueden registrar siembras en lotes disponibles.',
            ], 422);
        }

        $variedad = Variedad::findOrFail($validated['id_variedad']);

        // Criterios 2 y 3: Cálculo automático de fecha de cosecha estimada si no se proporcionó
        if (empty($validated['fecha_cosecha_estimada'])) {
            $validated['fecha_cosecha_estimada'] = \Carbon\Carbon::parse($validated['fecha_siembra'])
                ->addDays($variedad->dias_cosecha_promedio ?: 90)
                ->toDateString();
        }

        // Generar código único si no se envió
        if (empty($validated['codigo'])) {
            $prefijo = strtoupper(substr($variedad->nombre, 0, 3));
            $validated['codigo'] = sprintf('%s-%s-%s', $prefijo, $lote->identificador, date('Ymd-His'));
        }

        $validated['registrado_por'] = $usuario->id;
        $validated['estado']         = 'sembrado';
        $validated['activo_en_lote'] = 1;

        if ($request->hasFile('fotografia')) {
            $validated['fotografia'] = $request->file('fotografia')->store('cultivos', 'public');
        }

        $cultivo = DB::transaction(function () use ($validated, $lote) {
            $nuevo = Cultivo::create($validated);
            // Marcar el lote como ocupado
            $lote->update(['estado' => 'ocupado']);
            return $nuevo;
        });

        $cultivo->load(['lote', 'variedad.tipoCultivo', 'registradoPor']);

        return response()->json([
            'message' => 'Cultivo registrado exitosamente.',
            'data'    => new CultivoResource($cultivo),
        ], 201);
    }

    /**
     * Ver detalle completo de un cultivo.
     */
    public function show(Request $request, Cultivo $cultivo): JsonResponse
    {
        $usuario = $request->user();

        if ($usuario->esTrabajador() && ! $cultivo->lote?->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            return response()->json(['message' => 'No tienes permisos para ver este cultivo.'], 403);
        }

        $cultivo->load([
            'lote',
            'variedad.tipoCultivo',
            'registradoPor',
            'actividades.tipoActividad',
            'actividades.asignadoA',
            'fotos',
        ]);

        return response()->json([
            'data' => new CultivoResource($cultivo),
        ]);
    }

    /**
     * Finalizar o registrar cosecha de un cultivo.
     */
    public function finalizar(Request $request, Cultivo $cultivo): JsonResponse
    {
        $validated = $request->validate([
            'fecha_cosecha_real'    => ['required', 'date'],
            'cantidad_cosechada_kg' => ['required', 'numeric', 'min:0'],
            'estado_siguiente_lote' => ['nullable', 'string', Rule::in(['disponible', 'en_descanso'])],
            'observaciones'         => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($cultivo, $validated) {
            $cultivo->update([
                'fecha_cosecha_real'    => $validated['fecha_cosecha_real'],
                'cantidad_cosechada_kg' => $validated['cantidad_cosechada_kg'],
                'estado'                => 'cosechado',
                'activo_en_lote'        => null,
                'observaciones'         => $validated['observaciones'] ?? $cultivo->observaciones,
            ]);

            // Liberar el lote
            $estadoLote = $validated['estado_siguiente_lote'] ?? 'disponible';
            $cultivo->lote?->update(['estado' => $estadoLote]);
        });

        $cultivo->load(['lote', 'variedad.tipoCultivo']);

        return response()->json([
            'message' => 'Cosecha registrada y cultivo finalizado con éxito.',
            'data'    => new CultivoResource($cultivo),
        ]);
    }
}
