<?php

namespace App\Http\Controllers;

use App\Models\AsignacionLote;
use App\Models\Lote;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AsignacionController extends Controller
{
    /**
     * Listado general de asignaciones y panel de gestión.
     */
    public function index(Request $request)
    {
        $trabajadores = Usuario::with([
            'lotes' => fn($q) => $q->orderBy('identificador'),
            'actividadesAsignadas' => function ($query) {
                $query->whereIn('estado', ['pendiente', 'en_progreso'])
                      ->with(['cultivo.lote', 'tipoActividad'])
                      ->orderBy('fecha_programada', 'asc');
            },
            'actividadesCompletadas' => function ($query) {
                $query->with(['cultivo.lote', 'tipoActividad'])
                      ->orderBy('fecha_programada', 'desc');
            }
        ])
        ->where('id_rol', 2)
        ->where('activo', true)
        ->orderBy('nombre')
        ->get();

        $lotes = Lote::where('activo', true)->orderBy('identificador')->get();

        return view('asignaciones.index', compact('trabajadores', 'lotes'));
    }

    /**
     * Criterio 1, 2, 3: Asignar un lote a un trabajador activo.
     */
    public function asignarLote(Request $request)
    {
        $request->validate([
            'id_usuario' => ['required', 'exists:usuarios,id'],
            'id_lote'    => ['required', 'exists:lotes,id'],
        ]);

        $usuario = Usuario::findOrFail($request->id_usuario);
        $lote = Lote::findOrFail($request->id_lote);

        // Criterio 3: Validar rol permitido para asignación (solo Trabajador activo)
        if (! $usuario->esTrabajador()) {
            throw ValidationException::withMessages([
                'id_usuario' => 'Solo se pueden asignar lotes a usuarios con el rol de Trabajador.',
            ]);
        }

        if (! $usuario->activo) {
            throw ValidationException::withMessages([
                'id_usuario' => 'El trabajador seleccionado no se encuentra activo.',
            ]);
        }

        if (! $lote->activo) {
            throw ValidationException::withMessages([
                'id_lote' => 'El lote seleccionado no está habilitado para operar.',
            ]);
        }

        // Criterio 2: Evitar duplicidad de asignación activa
        $existeActiva = AsignacionLote::where('id_lote', $lote->id)
            ->where('id_usuario', $usuario->id)
            ->where('activo', true)
            ->exists();

        if ($existeActiva) {
            throw ValidationException::withMessages([
                'id_usuario' => 'El trabajador ya tiene una asignación activa sobre este lote.',
            ]);
        }

        // Criterio 1: Crear asignación activa
        $claveActiva = "lote_{$lote->id}_user_{$usuario->id}";

        AsignacionLote::create([
            'id_usuario'   => $usuario->id,
            'id_lote'      => $lote->id,
            'asignado_por' => $request->user()->id,
            'activo'       => true,
            'clave_activa' => $claveActiva,
            'creado_en'    => now(),
        ]);

        return back()->with('success', "Lote {$lote->identificador} asignado correctamente al trabajador {$usuario->nombre}.");
    }

    /**
     * Criterio 5: Desactivar asignación sin perder historial.
     */
    public function desactivarLote(Request $request)
    {
        $request->validate([
            'id_usuario' => ['required', 'exists:usuarios,id'],
            'id_lote'    => ['required', 'exists:lotes,id'],
        ]);

        $asignacion = AsignacionLote::where('id_lote', $request->id_lote)
            ->where('id_usuario', $request->id_usuario)
            ->where('activo', true)
            ->first();

        if (! $asignacion) {
            throw ValidationException::withMessages([
                'id_lote' => 'No se encontró una asignación activa para el lote y trabajador seleccionados.',
            ]);
        }

        // Desactivar manteniendo el registro en el historial
        $asignacion->update([
            'activo'       => false,
            'clave_activa' => null,
        ]);

        return back()->with('success', 'Asignación desactivada exitosamente. Se conservó el registro histórico.');
    }

    /**
     * Criterio 6: Reasignación controlada de lote.
     */
    public function reasignarLote(Request $request)
    {
        $request->validate([
            'id_lote'             => ['required', 'exists:lotes,id'],
            'id_usuario_anterior' => ['required', 'exists:usuarios,id'],
            'id_usuario_nuevo'    => ['required', 'exists:usuarios,id', 'different:id_usuario_anterior'],
        ]);

        $lote = Lote::findOrFail($request->id_lote);
        $usuarioNuevo = Usuario::findOrFail($request->id_usuario_nuevo);

        // Validar rol de nuevo trabajador
        if (! $usuarioNuevo->esTrabajador()) {
            throw ValidationException::withMessages([
                'id_usuario_nuevo' => 'Solo se pueden reasignar lotes a usuarios con el rol de Trabajador.',
            ]);
        }

        if (! $usuarioNuevo->activo) {
            throw ValidationException::withMessages([
                'id_usuario_nuevo' => 'El nuevo trabajador seleccionado no se encuentra activo.',
            ]);
        }

        // Validar que el nuevo trabajador no tenga ya asignación activa
        $existeEnNuevo = AsignacionLote::where('id_lote', $lote->id)
            ->where('id_usuario', $usuarioNuevo->id)
            ->where('activo', true)
            ->exists();

        if ($existeEnNuevo) {
            throw ValidationException::withMessages([
                'id_usuario_nuevo' => 'El nuevo trabajador ya tiene una asignación activa sobre este lote.',
            ]);
        }

        // 1. Cerrar la asignación anterior manteniendo el historial
        $asignacionAnterior = AsignacionLote::where('id_lote', $lote->id)
            ->where('id_usuario', $request->id_usuario_anterior)
            ->where('activo', true)
            ->first();

        if ($asignacionAnterior) {
            $asignacionAnterior->update([
                'activo'       => false,
                'clave_activa' => null,
            ]);
        }

        // 2. Registrar la nueva asignación activa
        AsignacionLote::create([
            'id_usuario'   => $usuarioNuevo->id,
            'id_lote'      => $lote->id,
            'asignado_por' => $request->user()->id,
            'activo'       => true,
            'clave_activa' => "lote_{$lote->id}_user_{$usuarioNuevo->id}",
            'creado_en'    => now(),
        ]);

        return back()->with('success', "Lote {$lote->identificador} reasignado exitosamente al trabajador {$usuarioNuevo->nombre}.");
    }
}
