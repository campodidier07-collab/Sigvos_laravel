<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\FotoCultivo;
use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoCultivoController extends Controller
{
    /**
     * Muestra la galería general de fotos.
     */
    public function index(Request $request)
    {
        // 1. Portadas de Lotes (que tienen imagen)
        $lotes = Lote::whereNotNull('fotografia')->orderBy('identificador')->get();

        // 2. Portadas de Cultivos (que tienen imagen)
        $cultivos = Cultivo::with(['lote', 'variedad'])->whereNotNull('fotografia')->orderBy('codigo')->get();

        // 3. Evidencias de Actividades
        $actividades = Actividad::with(['cultivo.lote', 'tipoActividad', 'ejecutadoPor'])
            ->whereNotNull('fotografia')
            ->orderBy('fecha_programada', 'desc')
            ->get();

        return view('fotografias.index', compact('lotes', 'cultivos', 'actividades'));
    }

    /**
     * Sube una nueva foto para un cultivo.
     */
    public function store(Request $request, Cultivo $cultivo)
    {
        // Administradores y trabajadores pueden subir fotos, pero el trabajador debe estar asignado
        $usuario = $request->user();
        if ($usuario->esTrabajador() && !$cultivo->lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            abort(403, 'No tienes permiso para subir fotos a este cultivo.');
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'descripcion' => 'nullable|string|max:500'
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            // Guardar en storage/app/public/cultivos
            $ruta = $file->store('cultivos', 'public');

            FotoCultivo::create([
                'id_cultivo' => $cultivo->id,
                'id_usuario' => $usuario->id,
                'ruta'       => $ruta,
                'descripcion'=> $request->descripcion,
            ]);

            return back()->with('success', 'Fotografía subida correctamente.');
        }

        return back()->with('error', 'Ocurrió un problema al subir la imagen.');
    }

    /**
     * Elimina una foto.
     */
    public function destroy(Request $request, FotoCultivo $foto)
    {
        $usuario = $request->user();

        // Solo admin o el que la subió puede borrarla
        if (!$usuario->isAdmin() && $foto->id_usuario !== $usuario->id) {
            abort(403, 'No tienes permiso para borrar esta foto.');
        }

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($foto->ruta)) {
            Storage::disk('public')->delete($foto->ruta);
        }

        $foto->delete();

        return back()->with('success', 'Fotografía eliminada.');
    }
}
