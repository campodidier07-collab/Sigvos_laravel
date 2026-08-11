<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Muestra todas las notificaciones del usuario actual.
     */
    public function index(Request $request)
    {
        $notificaciones = $request->user()->notificaciones()
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('notificaciones.index', compact('notificaciones'));
    }

    /**
     * Marca una notificación como leída.
     */
    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        // Verificar que la notificacion pertenezca al usuario
        if ($notificacion->id_usuario !== $request->user()->id) {
            abort(403);
        }

        $notificacion->update(['leida' => true]);

        // Si tiene una URL de destino, redirigimos hacia allá
        if ($notificacion->url) {
            return redirect($notificacion->url);
        }

        return back();
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function marcarTodasLeidas(Request $request)
    {
        $request->user()->notificaciones()
            ->where('leida', false)
            ->update(['leida' => true]);

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }
}
