<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\TipoActividad;
use App\Models\Usuario;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    /**
     * Listado de actividades.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        $query = Actividad::with(['cultivo.lote', 'tipoActividad', 'asignadoA', 'creadoPor']);

        // Si es trabajador, solo ve las actividades asignadas a él
        if ($usuario->esTrabajador()) {
            $query->where('asignado_a', $usuario->id);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
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

        $actividades = $query->orderBy('fecha_programada')->paginate(15);

        return view('actividades.index', compact('actividades'));
    }

    /**
     * Formulario para crear una actividad.
     */
    public function create(Request $request)
    {
        $usuario = $request->user();

        // Obtener cultivos activos para asignarles la actividad
        $cultivosQuery = Cultivo::with(['lote', 'variedad.tipoCultivo'])
                                ->whereNotNull('activo_en_lote');
                                
        if ($usuario->esTrabajador()) {
            $cultivosQuery->whereHas('lote.trabajadores', function($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }
        $cultivos = $cultivosQuery->get();

        $tiposActividad = TipoActividad::where('activo', true)->get();
        
        // Solo obtener trabajadores activos para poder asignarles la tarea
        $trabajadores = Usuario::where('id_rol', 2)->where('activo', true)->get();
        
        $cultivoPreseleccionado = $request->query('cultivo');

        return view('actividades.create', compact('cultivos', 'tiposActividad', 'trabajadores', 'cultivoPreseleccionado'));
    }

    /**
     * Guardar la actividad.
     */
    public function store(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'id_cultivo'        => ['required', 'exists:cultivos,id'],
            'id_tipo_actividad' => ['required', 'exists:tipos_actividad,id'],
            'asignado_a'        => ['required', 'exists:usuarios,id'],
            'fecha_programada'  => ['required', 'date', 'after_or_equal:today'],
            'descripcion'       => ['required', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
        ]);

        $datos['creado_por'] = $usuario->id;
        $datos['estado'] = 'pendiente';

        $actividad = Actividad::create($datos);

        // Notificaciones
        if ($usuario->esTrabajador()) {
            // Notificar a todos los administradores
            $admins = \App\Models\Usuario::where('id_rol', 1)->where('activo', true)->get();
            foreach ($admins as $admin) {
                \App\Models\Notificacion::create([
                    'id_usuario' => $admin->id,
                    'id_actividad' => $actividad->id,
                    'tipo' => 'actividad',
                    'prioridad' => 'normal',
                    'titulo' => 'Nueva actividad registrada',
                    'mensaje' => "El trabajador {$usuario->nombre} ha registrado una nueva actividad: {$actividad->descripcion}",
                ]);
            }
        } elseif ($usuario->isAdmin()) {
            // Notificar al trabajador asignado
            if ($actividad->asignado_a !== $usuario->id) {
                \App\Models\Notificacion::create([
                    'id_usuario' => $actividad->asignado_a,
                    'id_actividad' => $actividad->id,
                    'tipo' => 'actividad',
                    'prioridad' => 'alta',
                    'titulo' => 'Nueva actividad asignada',
                    'mensaje' => "Se te ha asignado una nueva actividad: {$actividad->descripcion}",
                ]);
            }
        }

        return redirect()->route('actividades.index')->with('success', 'Actividad programada correctamente.');
    }

    /**
     * Mostrar detalles.
     */
    public function show(Actividad $actividade)
    {
        $actividad = $actividade; // Fix parameter binding logic
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

        $cultivos = Cultivo::where('id', $actividad->id_cultivo)->get(); // Solo lectura prácticamente
        $tiposActividad = TipoActividad::where('activo', true)->get();
        $trabajadores = Usuario::where('id_rol', 2)->where('activo', true)->get();

        return view('actividades.edit', compact('actividad', 'cultivos', 'tiposActividad', 'trabajadores'));
    }

    /**
     * Actualizar estado o datos de la actividad.
     */
    public function update(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            abort(403);
        }

        $reglas = [
            'estado'            => ['required', 'in:pendiente,completada,cancelada'],
            'descripcion'       => ['required', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
            'fotografia'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ];

        // Solo el admin puede cambiar asignación, fechas o el tipo una vez creada
        if ($usuario->isAdmin()) {
            $reglas['id_tipo_actividad'] = ['required', 'exists:tipos_actividad,id'];
            $reglas['asignado_a']        = ['required', 'exists:usuarios,id'];
            $reglas['fecha_programada']  = ['required', 'date'];
        }

        // Si se marca como completada, se exige la fecha de ejecución (o se toma hoy por defecto)
        if ($request->estado == 'completada') {
            $reglas['fecha_ejecucion'] = ['nullable', 'date'];
        }

        $datos = $request->validate($reglas);

        if ($datos['estado'] == 'completada' && empty($actividad->ejecutado_por)) {
            $datos['ejecutado_por'] = $usuario->id;
            if (empty($request->fecha_ejecucion)) {
                $datos['fecha_ejecucion'] = now();
            }
        } elseif (in_array($datos['estado'], ['pendiente', 'cancelada'])) {
            $datos['ejecutado_por'] = null;
            $datos['fecha_ejecucion'] = null;
        }

        if ($request->hasFile('fotografia')) {
            if ($actividad->fotografia && \Illuminate\Support\Facades\Storage::disk('public')->exists($actividad->fotografia)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($actividad->fotografia);
            }
            $datos['fotografia'] = $request->file('fotografia')->store('actividades_evidencia', 'public');
        }

        $actividad->update($datos);

        return redirect()->route('actividades.index')->with('success', 'Actividad actualizada.');
    }

    /**
     * Eliminar (Solo admin).
     */
    public function destroy(Request $request, Actividad $actividade)
    {
        $actividad = $actividade;
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $actividad->delete();

        return redirect()->route('actividades.index')->with('success', 'Actividad eliminada.');
    }
}
