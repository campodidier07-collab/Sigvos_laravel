<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\TipoCultivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LoteController extends Controller
{
    /**
     * Mostrar la lista de lotes.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Base query
        $query = Lote::with(['tipoPreferido', 'cultivoActivo.variedad']);

        // Si es trabajador, solo ve los lotes que tiene asignados
        if ($usuario->esTrabajador()) {
            $query->whereHas('trabajadores', function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }

        // Búsqueda simple
        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');
            $query->where(function ($q) use ($busqueda) {
                $q->where('identificador', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('ubicacion', 'like', "%{$busqueda}%");
            });
        }

        // Calcular totales por estado para los KPIs ANTES del orderBy y paginate
        $queryStats = clone $query;
        $estadosRaw = $queryStats->select('estado', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                                 ->groupBy('estado')
                                 ->get();

        $lotes = $query->orderBy('identificador')->paginate(10);
        $estadosLotes = [];
        // Asegurar que existan los 4 estados principales
        foreach(['disponible', 'ocupado', 'en_descanso', 'inactivo'] as $est) {
            $estadosLotes[$est] = 0;
        }
        foreach($estadosRaw as $row) {
            $estadosLotes[$row->estado] = $row->total;
        }

        return view('lotes.index', compact('lotes', 'estadosLotes'));
    }

    /**
     * Mostrar el formulario para crear un nuevo lote.
     * Solo para admin (el middleware role:admin lo protege en web.php si lo configuramos,
     * pero para estar seguros podemos verificar aquí).
     */
    public function create(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'No tienes permiso para crear lotes.');
        }

        $tiposCultivo = TipoCultivo::where('activo', true)->get();
        return view('lotes.create', compact('tiposCultivo'));
    }

    /**
     * Guardar el nuevo lote.
     */
    public function store(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $datos = $request->validate([
            'identificador'     => 'required|string|size:1|unique:lotes,identificador',
            'nombre'            => 'required|string|max:100',
            'ubicacion'         => 'required|string|max:200',
            'area_ha'           => 'required|numeric|min:0.01',
            'id_tipo_preferido' => 'nullable|exists:tipos_cultivo,id',
            'es_alternativo'    => 'boolean',
            'fotografia'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $datos['es_alternativo'] = $request->has('es_alternativo');
        $datos['estado'] = 'disponible';
        $datos['activo'] = true;

        if ($request->hasFile('fotografia')) {
            $datos['fotografia'] = $request->file('fotografia')->store('lotes', 'public');
        }

        Lote::create($datos);

        return redirect()->route('lotes.index')->with('success', 'Lote creado exitosamente.');
    }

    /**
     * Mostrar el detalle del lote.
     */
    public function show(Lote $lote)
    {
        $lote->load(['tipoPreferido', 'cultivoActivo.variedad.tipoCultivo', 'trabajadores', 'cultivos' => function($q) {
            $q->orderByDesc('fecha_siembra');
        }]);

        return view('lotes.show', compact('lote'));
    }

    /**
     * Mostrar formulario para editar.
     */
    public function edit(Request $request, Lote $lote)
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $tiposCultivo = TipoCultivo::where('activo', true)->get();
        return view('lotes.edit', compact('lote', 'tiposCultivo'));
    }

    /**
     * Actualizar el lote.
     */
    public function update(Request $request, Lote $lote)
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $datos = $request->validate([
            'identificador'     => 'required|string|size:1|unique:lotes,identificador,' . $lote->id,
            'nombre'            => 'required|string|max:100',
            'ubicacion'         => 'required|string|max:200',
            'area_ha'           => 'required|numeric|min:0.01',
            'id_tipo_preferido' => 'nullable|exists:tipos_cultivo,id',
            'es_alternativo'    => 'boolean',
            'activo'            => 'boolean',
            'fotografia'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $datos['es_alternativo'] = $request->has('es_alternativo');
        $datos['activo'] = $request->has('activo');

        if ($request->hasFile('fotografia')) {
            // Delete old photo if it exists
            if ($lote->fotografia && Storage::disk('public')->exists($lote->fotografia)) {
                Storage::disk('public')->delete($lote->fotografia);
            }
            $datos['fotografia'] = $request->file('fotografia')->store('lotes', 'public');
        }

        $lote->update($datos);

        return redirect()->route('lotes.index')->with('success', 'Lote actualizado exitosamente.');
    }

    /**
     * Eliminar (o desactivar lógicamente).
     */
    public function destroy(Request $request, Lote $lote)
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        // Mejor hacer soft delete lógico (activo = false) porque puede tener cultivos
        if ($lote->cultivos()->count() > 0) {
            $lote->update(['activo' => false]);
            return redirect()->route('lotes.index')->with('success', 'El lote se ha desactivado porque tiene historial de cultivos.');
        }

        $lote->delete();
        return redirect()->route('lotes.index')->with('success', 'Lote eliminado definitivamente.');
    }
}
