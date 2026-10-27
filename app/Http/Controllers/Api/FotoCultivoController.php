<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\FotoCultivo;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoCultivoController extends Controller
{
    /**
     * Galería consolidada de fotografías y evidencias agrícolas.
     */
    public function index(Request $request): JsonResponse
    {
        $idCultivo = $request->query('id_cultivo');

        $query = FotoCultivo::with(['cultivo.lote', 'usuario'])
            ->orderByDesc('fecha_captura');

        if ($idCultivo) {
            $query->where('id_cultivo', $idCultivo);
        }

        $fotos = $query->paginate(24)->through(function ($f) {
            return [
                'id'            => $f->id,
                'id_cultivo'    => $f->id_cultivo,
                'cultivo_codigo'=> $f->cultivo?->codigo ?? 'Cultivo',
                'lote_nombre'   => $f->cultivo?->lote?->nombre ?? 'Lote',
                'url'           => asset('storage/' . $f->ruta),
                'descripcion'   => $f->descripcion,
                'fecha_captura' => $f->fecha_captura?->toIso8601String(),
                'subido_por'    => $f->usuario?->nombre ?? 'Usuario',
            ];
        });

        return response()->json([
            'data' => $fotos,
        ]);
    }

    /**
     * Subir una nueva fotografía como evidencia para un cultivo.
     */
    public function store(Request $request, Cultivo $cultivo): JsonResponse
    {
        $usuario = $request->user();

        if ($usuario->esTrabajador() && ! $cultivo->lote?->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            return response()->json(['message' => 'No tienes asignado el lote de este cultivo.'], 403);
        }

        $request->validate([
            'foto'        => ['required', 'image', 'max:10240'], // Hasta 10MB
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('foto');
        $ruta = $file->store('cultivos', 'public');

        $foto = FotoCultivo::create([
            'id_cultivo'    => $cultivo->id,
            'id_usuario'    => $usuario->id,
            'ruta'          => $ruta,
            'descripcion'   => $request->descripcion,
            'fecha_captura' => now(),
        ]);

        $foto->load(['cultivo.lote', 'usuario']);

        return response()->json([
            'message' => 'Fotografía subida correctamente.',
            'data'    => [
                'id'            => $foto->id,
                'id_cultivo'    => $foto->id_cultivo,
                'cultivo_codigo'=> $foto->cultivo?->codigo,
                'lote_nombre'   => $foto->cultivo?->lote?->nombre,
                'url'           => asset('storage/' . $foto->ruta),
                'descripcion'   => $foto->descripcion,
                'fecha_captura' => $foto->fecha_captura?->toIso8601String(),
                'subido_por'    => $foto->usuario?->nombre,
            ],
        ], 201);
    }

    /**
     * Eliminar una fotografía.
     */
    public function destroy(Request $request, FotoCultivo $foto): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario->isAdmin() && $foto->id_usuario !== $usuario->id) {
            return response()->json(['message' => 'No tienes permiso para borrar esta foto.'], 403);
        }

        if (Storage::disk('public')->exists($foto->ruta)) {
            Storage::disk('public')->delete($foto->ruta);
        }

        $foto->delete();

        return response()->json([
            'message' => 'Fotografía eliminada correctamente.',
        ]);
    }
}
