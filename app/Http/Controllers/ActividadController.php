<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Notificacion;
use App\Models\TipoActividad;
use App\Models\Usuario;
use App\Services\RecordatorioService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    /**
     * Listado de actividades (HU DP-10 y HU DP-13).
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Criterio 2, 3, 4 (HU DP-10): Evaluar alertas agronómicas y recordatorios automáticos
        if ($usuario->isAdmin()) {
            app(\App\Services\RecordatorioService::class)->evaluarAlertasYRecordatorios();
        }

        $query = Actividad::with(['cultivo.lote', 'tipoActividad', 'asignadoA', 'creadoPor']);

        // Si es trabajador, solo ve las actividades asignadas a él
        if ($usuario->esTrabajador()) {
            $query->where('asignado_a', $usuario->id);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // HU DP-13 Criterio 4: Consulta de historial de actividades por cultivo
        if ($request->filled('id_cultivo')) {
            $query->where('id_cultivo', $request->id_cultivo);
        }

        // HU DP-13 Criterio 5: Consulta de historial por tipo de actividad
        if ($request->filled('id_tipo_actividad')) {
            $query->where('id_tipo_actividad', $request->id_tipo_actividad);
        }

        // Búsqueda por cultivo o descripción
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function($q) use ($busqueda) {
                $q->whereHas('cultivo', function($c) use ($busqueda) {
                    $c->where('codigo', 'like', "%{$busqueda}%");
                })->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }

        $actividades = $query->orderByDesc('fecha_programada')->paginate(15);
        $tiposActividad = TipoActividad::where('activo', true)->orderBy('nombre')->get();
        $cultivos = Cultivo::with('lote')->whereNotNull('activo_en_lote')->orderBy('codigo')->get();

        return view('actividades.index', compact('actividades', 'tiposActividad', 'cultivos'));
    }

    /**
     * Formulario para crear una actividad.
     */
    public function create(Request $request)
    {
        $usuario = $request->user();

        // Obtener cultivos activos con lote y trabajadores asignados
        $cultivosQuery = Cultivo::with(['lote.trabajadores', 'variedad.tipoCultivo'])
                                ->whereNotNull('activo_en_lote');
                                
        if ($usuario->esTrabajador()) {
            $cultivosQuery->whereHas('lote.trabajadores', function($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }
        $cultivos = $cultivosQuery->get();

        $tiposActividad = TipoActividad::where('activo', true)->get();
        
        // Eager load lotes asignados a los trabajadores activos
        $trabajadores = Usuario::with('lotes')->where('id_rol', 2)->where('activo', true)->get();
        
        $cultivoPreseleccionado = $request->query('cultivo');

        return view('actividades.create', compact('cultivos', 'tiposActividad', 'trabajadores', 'cultivoPreseleccionado'));
    }

    /**
     * Guardar la actividad programada (HU DP-10 y HU DP-13).
     */
    public function store(Request $request)
    {
        $usuario = $request->user();

        // HU DP-13 Criterio 6: Validación de descripción mínima (mínimo 10 caracteres)
        // HU DP-10 Criterio 8: Validación estricta de fecha futura para programación
        $datos = $request->validate([
            'id_cultivo'        => ['required', 'exists:cultivos,id'],
            'id_tipo_actividad' => ['required', 'exists:tipos_actividad,id'],
            'asignado_a'        => ['required', 'exists:usuarios,id'],
            'fecha_programada'  => ['required', 'date', 'after_or_equal:today'],
            'descripcion'       => ['required', 'string', 'min:10', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
        ], [
            'fecha_programada.after_or_equal' => 'La fecha de programación no puede ser una fecha pasada. Debe ser hoy o una fecha futura.',
            'descripcion.min'                 => 'La descripción ingresada es insuficiente. Debe ingresar al menos 10 caracteres.',
        ]);

        $datos['creado_por'] = $usuario->id;
        $datos['estado'] = 'pendiente';

        $actividad = Actividad::create($datos);

        $cultivo = Cultivo::with('lote.trabajadores')->find($datos['id_cultivo']);
        $tieneAsignacionLote = $cultivo && $cultivo->lote && $cultivo->lote->trabajadores->contains('id', $datos['asignado_a']);

        // Notificaciones iniciales de asignación
        if ($usuario->esTrabajador()) {
            $admins = Usuario::where('id_rol', 1)->where('activo', true)->get();
            foreach ($admins as $admin) {
                Notificacion::create([
                    'id_usuario'   => $admin->id,
                    'id_actividad' => $actividad->id,
                    'tipo'         => 'actividad',
                    'prioridad'    => 'normal',
                    'titulo'       => 'Nueva actividad registrada',
                    'mensaje'      => "El trabajador {$usuario->nombre} ha registrado una nueva actividad: {$actividad->descripcion}",
                    'creado_en'    => now(),
                ]);
            }
        } elseif ($usuario->isAdmin()) {
            if ($actividad->asignado_a !== $usuario->id) {
                $mensajeNotif = "Se te ha asignado una nueva actividad en el cultivo {$cultivo->codigo} (Lote {$cultivo->lote->identificador}): {$actividad->descripcion}";
                if (!$tieneAsignacionLote) {
                    $mensajeNotif .= " [Nota: Favor coordinar con administración para acceso a este lote].";
                }

                Notificacion::create([
                    'id_usuario'   => $actividad->asignado_a,
                    'id_actividad' => $actividad->id,
                    'tipo'         => 'actividad',
                    'prioridad'    => 'alta',
                    'titulo'       => 'Nueva actividad asignada',
                    'mensaje'      => $mensajeNotif,
                    'creado_en'    => now(),
                ]);
            }
        }

        // Evaluar si califica para recordatorio inmediato
        app(\App\Services\RecordatorioService::class)->evaluarAlertasYRecordatorios();

        $mensajeExito = 'Actividad programada correctamente.';
        if (!$tieneAsignacionLote && $usuario->isAdmin()) {
            $mensajeExito .= ' (Aviso: El trabajador asignado no tiene asignación formal activa en el lote de este cultivo).';
        }

        if ($request->input('redirect_to') === 'calendario') {
            return redirect()->route('calendario.index')->with('success', $mensajeExito);
        }

        return redirect()->route('actividades.index')->with('success', $mensajeExito);
    }

    /**
     * HU DP-13 & HU DP-14: Registrar directamente una labor agrícola completada/realizada.
     * Criterio 1: Registro exitoso de actividad realizada (tipo, descripción, cultivo).
     * Criterio 2: Validación de descripción mínima (min:10).
     * Criterio 3: Restricción sobre cultivos finalizados (cosechado, perdido o inactivo).
     * Criterio 4: Asignación automática de fecha de ejecución si se omite.
     * Criterio 5: Bloqueo de registro en cultivos no asignados al trabajador (403).
     */
    public function registrarRealizada(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'id_cultivo'        => ['required', 'exists:cultivos,id'],
            'id_tipo_actividad' => ['required', 'exists:tipos_actividad,id'],
            'fecha_realizacion' => ['nullable', 'date', 'before_or_equal:today'],
            'descripcion'       => ['required', 'string', 'min:10', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
            'fotografia'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ], [
            'fecha_realizacion.before_or_equal' => 'La fecha de la actividad realizada no puede ser una fecha futura.',
            'descripcion.min'                   => 'La descripción de la labor realizada es insuficiente. Debe ingresar al menos 10 caracteres.',
        ]);

        $cultivo = Cultivo::with('lote')->findOrFail($datos['id_cultivo']);

        // HU DP-14 Criterio 5: Bloqueo en cultivos no asignados al trabajador
        if ($usuario && $usuario->esTrabajador()) {
            $asignado = $cultivo->lote && $cultivo->lote->trabajadores()->where('id_usuario', $usuario->id)->exists();
            if (!$asignado) {
                abort(403, 'No tienes permisos para registrar actividades en un cultivo de un lote no asignado.');
            }
        }

        // HU DP-14 Criterio 3: Restricción sobre cultivos finalizados
        if (in_array($cultivo->estado, ['cosechado', 'perdido']) || !$cultivo->estaActivo()) {
            return back()->withInput()->withErrors([
                'id_cultivo' => 'No se pueden registrar actividades en un cultivo que ya ha sido finalizado o cosechado.',
            ])->with('error', 'Operación rechazada: El cultivo ya ha finalizado su ciclo productivo.');
        }

        // HU DP-14 Criterio 4: Asignación automática de fecha de ejecución si se omite
        $fechaEjecucion = !empty($datos['fecha_realizacion'])
            ? $datos['fecha_realizacion']
            : \Carbon\Carbon::today()->toDateString();

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $request->file('fotografia')->store('actividades_evidencia', 'public');
        }

        $actividad = Actividad::create([
            'id_cultivo'        => $datos['id_cultivo'],
            'id_tipo_actividad' => $datos['id_tipo_actividad'],
            'creado_por'        => $usuario->id,
            'asignado_a'        => $usuario->id,
            'ejecutado_por'     => $usuario->id,
            'estado'            => 'completada',
            'fecha_programada'  => $fechaEjecucion,
            'fecha_ejecucion'   => $fechaEjecucion,
            'descripcion'       => $datos['descripcion'],
            'observaciones'     => $datos['observaciones'] ?? null,
            'fotografia'        => $datos['fotografia'] ?? null,
        ]);

        return redirect()->route('cultivos.show', $actividad->id_cultivo)
            ->with('success', 'Actividad realizada registrada correctamente en el historial.');
    }

    /**
     * Mostrar detalles.
     */
    public function show(Actividad $actividade)
    {
        $actividad = $actividade;
        $actividad->load(['cultivo.lote', 'tipoActividad', 'asignadoA', 'creadoPor', 'ejecutadoPor']);
        return view('actividades.show', compact('actividad'));
    }

    /**
     * Formulario de edición / reporte de ejecución.
     */
    public function edit(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            abort(403, 'No tienes permiso para editar esta actividad.');
        }

        // HU DP-12 Criterio 4: Restricción de modificación de actividades completadas
        if ($actividad->estado === 'completada') {
            return redirect()->route('actividades.show', $actividad)
                ->with('error', 'No se puede modificar una actividad que ya ha sido completada.');
        }

        // HU DP-12 Criterio 3: Permitir al admin seleccionar entre cultivos activos
        $cultivos = $usuario->isAdmin()
            ? Cultivo::with('lote')->whereNotNull('activo_en_lote')->orderBy('codigo')->get()
            : Cultivo::where('id', $actividad->id_cultivo)->get();

        $tiposActividad = TipoActividad::where('activo', true)->get();
        $trabajadores = Usuario::where('id_rol', 2)->where('activo', true)->get();

        // HU DP-12 Criterio 7: Detectar si pertenece a una serie recurrente
        $esRecurrente = !empty($actividad->grupo_recurrencia);
        $totalRecurrentes = $esRecurrente 
            ? Actividad::where('grupo_recurrencia', $actividad->grupo_recurrencia)->where('estado', '!=', 'completada')->count()
            : 1;

        return view('actividades.edit', compact('actividad', 'cultivos', 'tiposActividad', 'trabajadores', 'esRecurrente', 'totalRecurrentes'));
    }

    /**
     * Actualizar estado o datos de la actividad (HU DP-10 y HU DP-12).
     */
    public function update(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            abort(403);
        }

        // HU DP-12 Criterio 4: Restricción de modificación de actividades completadas
        if ($actividad->estado === 'completada') {
            return redirect()->route('actividades.show', $actividad)
                ->with('error', 'Operación denegada: La actividad ya fue completada y no permite modificaciones.');
        }

        $reglas = [
            'estado'            => ['required', 'in:pendiente,completada,cancelada'],
            'descripcion'       => ['required', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
            'fotografia'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'alcance_recurrencia' => ['nullable', 'in:solo_esta,todas_pendientes'],
        ];

        // Solo el admin puede cambiar cultivo objetivo, asignación, fechas o el tipo una vez creada
        if ($usuario->isAdmin()) {
            // HU DP-12 Criterio 3: Cambiar cultivo objetivo
            $reglas['id_cultivo']        = ['required', 'exists:cultivos,id'];
            $reglas['id_tipo_actividad'] = ['required', 'exists:tipos_actividad,id'];
            $reglas['asignado_a']        = ['required', 'exists:usuarios,id'];

            // HU DP-12 Criterio 6: Validación de fecha futura al reagendar
            $reglas['fecha_programada']  = ['required', 'date', 'after_or_equal:today'];
        }

        // Si se marca como completada, se exige la fecha de ejecución (o se toma hoy por defecto) y no puede ser futura (HU DP-15 Criterio 3)
        if ($request->estado == 'completada') {
            $reglas['fecha_ejecucion'] = ['nullable', 'date', 'before_or_equal:today'];
        }

        $mensajes = [
            'fecha_programada.after_or_equal' => 'La fecha de reagendamiento no puede ser una fecha pasada. Debe ser hoy o una fecha futura.',
            'fecha_ejecucion.before_or_equal'  => 'La fecha de ejecución no puede ser una fecha futura.',
        ];

        $datos = $request->validate($reglas, $mensajes);

        // HU DP-12 Criterio 1: Si cambió la fecha programada, cancelar recordatorios anteriores obsoletos
        if ($usuario->isAdmin() && isset($datos['fecha_programada']) && $actividad->fecha_programada != $datos['fecha_programada']) {
            app(\App\Services\RecordatorioService::class)->cancelarRecordatoriosActividad($actividad->id);
        }

        // Criterio 5: Marcar actividad programada como completada
        if ($datos['estado'] == 'completada') {
            if (empty($actividad->ejecutado_por)) {
                $datos['ejecutado_por'] = $usuario->id;
            }
            if (empty($request->fecha_ejecucion)) {
                $datos['fecha_ejecucion'] = now();
            }
            // Cancelar recordatorios pendientes de esta actividad
            app(\App\Services\RecordatorioService::class)->cancelarRecordatoriosActividad($actividad->id);
        } elseif (in_array($datos['estado'], ['pendiente', 'cancelada'])) {
            $datos['ejecutado_por'] = null;
            $datos['fecha_ejecucion'] = null;
            if ($datos['estado'] == 'cancelada') {
                app(\App\Services\RecordatorioService::class)->cancelarRecordatoriosActividad($actividad->id);
            }
        }

        if ($request->hasFile('fotografia')) {
            if ($actividad->fotografia && \Illuminate\Support\Facades\Storage::disk('public')->exists($actividad->fotografia)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($actividad->fotografia);
            }
            $datos['fotografia'] = $request->file('fotografia')->store('actividades_evidencia', 'public');
        }

        $alcanceRecurrencia = $request->input('alcance_recurrencia', 'solo_esta');

        // HU DP-12 Criterio 7: Modificación masiva de actividades recurrentes
        if ($alcanceRecurrencia === 'todas_pendientes' && !empty($actividad->grupo_recurrencia)) {
            $camposMasivos = [
                'descripcion'       => $datos['descripcion'],
                'observaciones'     => $datos['observaciones'] ?? null,
            ];
            if ($usuario->isAdmin()) {
                $camposMasivos['id_cultivo']        = $datos['id_cultivo'];
                $camposMasivos['id_tipo_actividad'] = $datos['id_tipo_actividad'];
                $camposMasivos['asignado_a']        = $datos['asignado_a'];
            }

            Actividad::where('grupo_recurrencia', $actividad->grupo_recurrencia)
                ->where('estado', 'pendiente')
                ->where('id', '!=', $actividad->id)
                ->update($camposMasivos);
        }

        unset($datos['alcance_recurrencia']);
        $actividad->update($datos);

        // Si fue reprogramada, evaluar nuevos recordatorios
        if ($datos['estado'] == 'pendiente') {
            app(\App\Services\RecordatorioService::class)->evaluarAlertasYRecordatorios();
        }

        // HU DP-12 Criterio 8: Notificación del sistema confirmando la actualización
        return redirect()->route('actividades.index')->with('success', 'Actividad actualizada correctamente.');
    }

    /**
     * HU DP-15 Criterio 2, 3, 4: Marcar actividad pendiente como realizada y adjuntar evidencia.
     */
    public function completar(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            abort(403, 'No tienes permisos para completar una actividad asignada a otro trabajador.');
        }

        $request->validate([
            'fecha_ejecucion' => ['nullable', 'date', 'before_or_equal:today'],
            'fotografia'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'observaciones'   => ['nullable', 'string', 'max:1000'],
        ], [
            'fecha_ejecucion.before_or_equal' => 'La fecha de ejecución no puede ser una fecha futura.',
        ]);

        $fechaEjecucion = $request->filled('fecha_ejecucion')
            ? $request->fecha_ejecucion
            : \Carbon\Carbon::today()->toDateString();

        $datosActualizar = [
            'estado'          => 'completada',
            'ejecutado_por'   => $usuario->id,
            'fecha_ejecucion' => $fechaEjecucion,
        ];

        if ($request->filled('observaciones')) {
            $datosActualizar['observaciones'] = $request->observaciones;
        }

        if ($request->hasFile('fotografia')) {
            if ($actividad->fotografia && \Illuminate\Support\Facades\Storage::disk('public')->exists($actividad->fotografia)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($actividad->fotografia);
            }
            $datosActualizar['fotografia'] = $request->file('fotografia')->store('actividades_evidencia', 'public');
        }

        $actividad->update($datosActualizar);

        app(\App\Services\RecordatorioService::class)->cancelarRecordatoriosActividad($actividad->id);

        return back()->with('success', 'Actividad registrada como realizada exitosamente.');
    }

    /**
     * Criterio 7: Cancelar actividad programada desde calendario o listado.
     */
    public function cancelar(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if (!$usuario->isAdmin() && $actividad->asignado_a !== $usuario->id) {
            abort(403);
        }

        // Cancelar recordatorios automáticos asociados
        app(\App\Services\RecordatorioService::class)->cancelarRecordatoriosActividad($actividad->id);

        $actividad->update(['estado' => 'cancelada']);

        return back()->with('success', 'Actividad cancelada y recordatorio eliminado.');
    }

    /**
     * Criterio 7: Eliminar actividad programada (Solo admin).
     */
    public function destroy(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        // Criterio 7: Elimina la actividad programada y cancela el recordatorio
        app(\App\Services\RecordatorioService::class)->cancelarRecordatoriosActividad($actividad->id);

        $actividad->delete();

        return redirect()->route('actividades.index')->with('success', 'Actividad eliminada y recordatorios cancelados.');
    }
}
