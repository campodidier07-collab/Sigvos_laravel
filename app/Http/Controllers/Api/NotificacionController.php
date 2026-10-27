<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Muestra las notificaciones del usuario autenticado con conteo de no leídas.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Notificacion::where('id_usuario', $user->id)
            ->with(['cultivo:id,codigo,variedad', 'actividad:id,descripcion,fecha_programada'])
            ->orderByDesc('creado_en');

        if ($request->filled('solo_no_leidas') && $request->boolean('solo_no_leidas')) {
            $query->where('leida', false);
        }

        $notificaciones = $query->paginate(20);
        $totalNoLeidas = Notificacion::where('id_usuario', $user->id)->where('leida', false)->count();

        return response()->json([
            'no_leidas' => $totalNoLeidas,
            'data' => $notificaciones->items(),
            'current_page' => $notificaciones->currentPage(),
            'last_page' => $notificaciones->lastPage(),
            'total' => $notificaciones->total(),
        ]);
    }

    /**
     * Marca una notificación como leída.
     */
    public function marcarLeida(Request $request, int $id): JsonResponse
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('id_usuario', $request->user()->id)
            ->firstOrFail();

        $notificacion->update([
            'leida' => true,
            'leida_en' => now(),
        ]);

        return response()->json([
            'message' => 'Notificación marcada como leída.',
            'notificacion' => $notificacion,
        ]);
    }

    /**
     * Marca todas las notificaciones del usuario como leídas.
     */
    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $afectadas = Notificacion::where('id_usuario', $request->user()->id)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now(),
            ]);

        return response()->json([
            'message' => 'Todas las notificaciones fueron marcadas como leídas.',
            'actualizadas' => $afectadas,
        ]);
    }
}
