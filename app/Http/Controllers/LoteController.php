<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Lote;
use App\Models\Notificacion;
use App\Models\TipoCultivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        if ($request->filled('identificador')) {
            $request->merge(['identificador' => strtoupper(trim($request->input('identificador')))]);
        }

        $datos = $request->validate([
            'identificador'     => 'required|string|size:1|unique:lotes,identificador',
            'nombre'            => 'required|string|max:100',
            'ubicacion'         => 'required|string|max:200',
            'area_ha'           => 'required|numeric|min:0.01',
            'id_tipo_preferido' => 'nullable|exists:tipos_cultivo,id',
            'es_alternativo'    => 'boolean',
            'fotografia'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], [
            'identificador.required' => 'El identificador del lote es obligatorio.',
            'identificador.unique'   => 'Este identificador de lote ya se encuentra registrado.',
            'identificador.size'     => 'El identificador debe ser de 1 solo carácter (ej. A, B, C).',
            'nombre.required'        => 'El nombre del lote es obligatorio.',
            'ubicacion.required'     => 'La ubicación del lote es obligatoria.',
            'area_ha.required'       => 'El área del lote en hectáreas es obligatoria.',
            'area_ha.min'            => 'El área del lote debe ser mayor a 0 hectáreas.',
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
    public function show(Request $request, Lote $lote)
    {
        $usuario = $request->user();

        // Criterio 4 (HU DP-8): Bloqueo de acceso a lotes no asignados para trabajadores
        if ($usuario && $usuario->esTrabajador() && ! $lote->trabajadores()->where('id_usuario', $usuario->id)->exists()) {
            abort(403, 'No tienes permisos para consultar este lote.');
        }

        $lote->load(['tipoPreferido', 'cultivoActivo.variedad.tipoCultivo', 'trabajadores', 'cultivos' => function($q) {
            $q->orderByDesc('fecha_siembra');
        }]);

        // Criterio 6: Cargar historial de modificaciones del lote
        $historialCambios = Auditoria::with('usuario')
            ->where('tabla', 'lotes')
            ->where('id_registro', (string)$lote->id)
            ->orderByDesc('ocurrido_en')
            ->get();

        $trabajadoresDisponibles = \App\Models\Usuario::where('id_rol', 2)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('lotes.show', compact('lote', 'historialCambios', 'trabajadoresDisponibles'));
    }

    /**
     * Mostrar formulario para editar.
     */
    public function edit(Request $request, Lote $lote)
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $lote->load('cultivoActivo.variedad');
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

        // Criterio 2: Restricción de modificación de identificador
        if ($request->filled('identificador') && strtoupper(trim($request->identificador)) !== strtoupper($lote->identificador)) {
            throw ValidationException::withMessages([
                'identificador' => 'El identificador del lote está bloqueado y no puede ser modificado.',
            ]);
        }

        // Criterio 5: Restricción de modificación con cultivo activo (datos sensibles como área)
        if ($lote->cultivoActivo()->exists() && $request->filled('area_ha') && abs((float)$request->area_ha - (float)$lote->area_ha) > 0.0001) {
            throw ValidationException::withMessages([
                'area_ha' => 'No se puede modificar el área de un lote que tiene un cultivo activo en desarrollo.',
            ]);
        }

        $datos = $request->validate([
            'identificador'     => 'nullable|string|size:1',
            'nombre'            => 'required|string|max:100',
            'ubicacion'         => 'required|string|max:200',
            'area_ha'           => 'required|numeric|min:0.01',
            'id_tipo_preferido' => 'nullable|exists:tipos_cultivo,id',
            'es_alternativo'    => 'boolean',
            'activo'            => 'boolean',
            'fotografia'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Asegurar que el identificador original no sea alterado
        $datos['identificador'] = $lote->identificador;
        $datos['es_alternativo'] = $request->has('es_alternativo');
        $datos['activo'] = $request->has('activo');

        if ($request->hasFile('fotografia')) {
            // Delete old photo if it exists
            if ($lote->fotografia && Storage::disk('public')->exists($lote->fotografia)) {
                Storage::disk('public')->delete($lote->fotografia);
            }
            $datos['fotografia'] = $request->file('fotografia')->store('lotes', 'public');
        }

        // Criterio 6: Capturar valores anteriores para historial de auditoría
        $atributosAuditables = ['nombre', 'ubicacion', 'area_ha', 'id_tipo_preferido', 'es_alternativo', 'activo'];
        $datosAntes = [];
        foreach ($atributosAuditables as $attr) {
            $datosAntes[$attr] = $lote->$attr;
        }

        $lote->update($datos);

        $cambios = $lote->getChanges();
        unset($cambios['updated_at']);

        if (! empty($cambios)) {
            $cambiosAntes = [];
            $cambiosDespues = [];
            foreach ($cambios as $col => $nuevoValor) {
                $cambiosAntes[$col] = $datosAntes[$col] ?? null;
                $cambiosDespues[$col] = $nuevoValor;
            }

            // Hardening DP-04 + DP-13: Notificar a trabajadores si el lote pasa a mantenimiento o inactivo
            if ($lote->estado === 'en_mantenimiento' || (isset($cambios['activo']) && !$lote->activo)) {
                foreach ($lote->trabajadores as $trabajador) {
                    Notificacion::create([
                        'id_usuario'   => $trabajador->id,
                        'tipo'         => 'alerta',
                        'prioridad'    => 'alta',
                        'titulo'       => "Lote {$lote->identificador} en mantenimiento/inactivo",
                        'mensaje'      => "El lote {$lote->identificador} ({$lote->nombre}) ha cambiado su estado. Verifica tus actividades asignadas.",
                        'creado_en'    => now(),
                    ]);
                }
            }

            Auditoria::create([
                'tabla'         => 'lotes',
                'id_registro'   => (string)$lote->id,
                'accion'        => 'UPDATE',
                'realizado_por' => $request->user()->id,
                'datos_antes'   => $cambiosAntes,
                'datos_despues' => $cambiosDespues,
                'ip_origen'     => $request->ip(),
                'ocurrido_en'   => now(),
            ]);
        }

        return redirect()->route('lotes.show', $lote)->with('success', 'Lote actualizado exitosamente.');
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
