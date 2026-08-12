<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Variedad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CultivoController extends Controller
{
    /**
     * Mostrar el listado de cultivos.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        
        $query = Cultivo::with(['lote', 'variedad.tipoCultivo', 'registradoPor']);
        
        // Trabajadores solo ven los cultivos de sus lotes asignados
        if ($usuario->esTrabajador()) {
            $query->whereHas('lote.trabajadores', function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por código o variedad
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhereHas('variedad', function($v) use ($busqueda) {
                      $v->where('nombre', 'like', "%{$busqueda}%");
                  });
            });
        }

        $cultivos = $query->orderByDesc('fecha_siembra')->paginate(10);
        
        return view('cultivos.index', compact('cultivos'));
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request)
    {
        // Administradores y Trabajadores pueden registrar cultivos (según los permisos o lógica de negocio).
        // Si quieres que solo admin, usa: if (!$request->user()->isAdmin()) abort(403);
        // Para SIGVOS, asumimos que ambos pueden (o se valida según el rol).
        
        $usuario = $request->user();
        
        // Obtener lotes disponibles (sin cultivo activo)
        // Para admin, todos. Para trabajador, solo sus asignados.
        $lotesQuery = Lote::where('activo', true)
                          ->whereDoesntHave('cultivos', function($q) {
                              $q->whereNotNull('activo_en_lote');
                          });
                          
        if ($usuario->esTrabajador()) {
            $lotesQuery->whereHas('trabajadores', function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }
        
        $lotes = $lotesQuery->get();
        $variedades = Variedad::with('tipoCultivo')->where('activo', true)->get();
        
        $lotePreseleccionado = $request->query('lote');

        return view('cultivos.create', compact('lotes', 'variedades', 'lotePreseleccionado'));
    }

    /**
     * Almacenar un nuevo cultivo.
     */
    public function store(Request $request)
    {
        $usuario = $request->user();
        
        $datos = $request->validate([
            'id_lote'                => ['required', 'exists:lotes,id'],
            'id_variedad'            => ['required', 'exists:variedades,id'],
            'codigo'                 => ['required', 'string', 'max:30', 'unique:cultivos,codigo'],
            'fecha_siembra'          => ['required', 'date', 'before_or_equal:today'],
            'fecha_cosecha_estimada' => ['required', 'date', 'after:fecha_siembra'],
            'observaciones'          => ['nullable', 'string', 'max:1000'],
            'fotografia'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);
        
        // Verificar que el lote no tenga ya un cultivo activo
        $lote = Lote::findOrFail($datos['id_lote']);
        if ($lote->cultivoActivo()->exists()) {
            return back()->withInput()->withErrors(['id_lote' => 'Este lote ya tiene un cultivo activo.']);
        }
        
        // Si es trabajador, verificar que esté asignado a ese lote
        if ($usuario->esTrabajador() && !$lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            abort(403, 'No estás asignado a este lote.');
        }

        $datos['registrado_por'] = $usuario->id;
        $datos['estado']         = 'sembrado';
        $datos['activo_en_lote'] = $lote->id; // El cultivo está activo en este lote

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $request->file('fotografia')->store('cultivos_portadas', 'public');
        }

        $cultivo = Cultivo::create($datos);

        // Actualizar el estado del lote a "ocupado"
        $lote->update(['estado' => 'ocupado']);

        return redirect()->route('cultivos.index')->with('success', 'Cultivo registrado exitosamente.');
    }

    /**
     * Mostrar detalles del cultivo.
     */
    public function show(Cultivo $cultivo)
    {
        $cultivo->load(['lote', 'variedad.tipoCultivo', 'registradoPor', 'actividades.tipoActividad', 'actividades.asignadoA', 'fotos.usuario']);
        return view('cultivos.show', compact('cultivo'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, Cultivo $cultivo)
    {
        // Verificación de permisos básicos
        $usuario = $request->user();
        if ($usuario->esTrabajador() && !$cultivo->lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            abort(403, 'No tienes permiso para editar este cultivo.');
        }

        $variedades = Variedad::with('tipoCultivo')->where('activo', true)->get();
        return view('cultivos.edit', compact('cultivo', 'variedades'));
    }

    /**
     * Actualizar cultivo (y gestionar fin de ciclo/cosecha).
     */
    public function update(Request $request, Cultivo $cultivo)
    {
        $usuario = $request->user();
        if ($usuario->esTrabajador() && !$cultivo->lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            abort(403);
        }

        $datos = $request->validate([
            'id_variedad'            => ['required', 'exists:variedades,id'],
            'codigo'                 => ['required', 'string', 'max:30', Rule::unique('cultivos', 'codigo')->ignore($cultivo->id)],
            'estado'                 => ['required', 'in:sembrado,creciendo,cosechado,perdido'],
            'fecha_siembra'          => ['required', 'date'],
            'fecha_cosecha_estimada' => ['required', 'date', 'after:fecha_siembra'],
            'fecha_cosecha_real'     => ['nullable', 'date', 'after_or_equal:fecha_siembra'],
            'cantidad_cosechada_kg'  => ['nullable', 'numeric', 'min:0'],
            'observaciones'          => ['nullable', 'string', 'max:1000'],
            'fotografia'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        // Lógica de finalización (cosechado o perdido)
        if (in_array($datos['estado'], ['cosechado', 'perdido'])) {
            $datos['activo_en_lote'] = null; // Liberar el lote
            
            // Validar que si es cosechado, tenga fecha y cantidad
            if ($datos['estado'] == 'cosechado') {
                if (empty($datos['fecha_cosecha_real']) || empty($datos['cantidad_cosechada_kg'])) {
                    return back()->withInput()->withErrors(['fecha_cosecha_real' => 'La fecha real y la cantidad son obligatorias al marcar como cosechado.']);
                }
            }
        } elseif ($cultivo->activo_en_lote === null) {
            // Intentar reactivarlo (volver a sembrado/creciendo)
            if ($cultivo->lote->cultivoActivo()->where('id', '!=', $cultivo->id)->exists()) {
                return back()->withInput()->withErrors(['estado' => 'No puedes reactivar este cultivo porque el lote ya tiene otro cultivo activo.']);
            }
            $datos['activo_en_lote'] = $cultivo->id_lote;
        }

        if ($request->hasFile('fotografia')) {
            // Delete old photo if it exists
            if ($cultivo->fotografia && Storage::disk('public')->exists($cultivo->fotografia)) {
                Storage::disk('public')->delete($cultivo->fotografia);
            }
            $datos['fotografia'] = $request->file('fotografia')->store('cultivos_portadas', 'public');
        }

        $cultivo->update($datos);

        // Si el lote se liberó, cambiar estado del lote a disponible
        if (array_key_exists('activo_en_lote', $datos) && $datos['activo_en_lote'] === null) {
            $cultivo->lote->update(['estado' => 'disponible']);
        } elseif ($cultivo->lote->estado == 'disponible') {
            $cultivo->lote->update(['estado' => 'ocupado']);
        }

        return redirect()->route('cultivos.index')->with('success', 'Cultivo actualizado correctamente.');
    }

    /**
     * Eliminar (Solo admin).
     */
    public function destroy(Request $request, Cultivo $cultivo)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        // Si tiene actividades, evitar borrado físico
        if ($cultivo->actividades()->count() > 0) {
            return redirect()->route('cultivos.index')->with('error', 'No se puede eliminar porque tiene actividades registradas. Marca como "perdido" si deseas cerrarlo.');
        }

        $lote = $cultivo->lote;
        $cultivo->delete();

        // Verificar si el lote se quedó vacío
        if (!$lote->cultivoActivo()->exists()) {
            $lote->update(['estado' => 'disponible']);
        }

        return redirect()->route('cultivos.index')->with('success', 'Cultivo eliminado permanentemente.');
    }
}
