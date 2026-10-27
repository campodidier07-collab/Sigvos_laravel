<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\TipoCultivo;
use App\Models\Usuario;
use App\Models\Variedad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Catálogo de tipos de cultivo con sus respectivas variedades.
     */
    public function tiposCultivo(): JsonResponse
    {
        $tipos = TipoCultivo::with(['variedades' => fn ($q) => $q->where('activo', true)])
            ->where('activo', true)
            ->get(['id', 'nombre', 'descripcion']);

        return response()->json([
            'data' => $tipos,
        ]);
    }

    /**
     * Catálogo de lotes disponibles para sembrar (sin cultivo activo en curso).
     */
    public function lotesDisponibles(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $query = Lote::where('activo', true)
            ->whereDoesntHave('cultivos', fn ($q) => $q->whereNotNull('activo_en_lote'));

        if ($usuario->esTrabajador()) {
            $query->whereHas('trabajadores', fn ($q) => $q->where('id_usuario', $usuario->id));
        }

        $lotes = $query->orderBy('identificador')->get(['id', 'identificador', 'nombre', 'ubicacion', 'area_ha', 'estado']);

        return response()->json([
            'data' => $lotes,
        ]);
    }

    /**
     * Catálogo de tipos de actividad agrícola (Riego, Fertilización, Poda, etc.).
     */
    public function tiposActividad(): JsonResponse
    {
        $tipos = TipoActividad::where('activo', true)->get(['id', 'nombre', 'descripcion']);

        return response()->json([
            'data' => $tipos,
        ]);
    }

    /**
     * Catálogo de trabajadores activos para asignación de labores.
     */
    public function trabajadores(): JsonResponse
    {
        $trabajadores = Usuario::where('id_rol', 2)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'email']);

        return response()->json([
            'data' => $trabajadores,
        ]);
    }

    /**
     * Catálogo de cultivos activos para vincularles actividades.
     */
    public function cultivosActivos(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $query = Cultivo::with(['lote', 'variedad'])
            ->whereNotNull('activo_en_lote');

        if ($usuario->esTrabajador()) {
            $query->whereHas('lote.trabajadores', fn ($q) => $q->where('id_usuario', $usuario->id));
        }

        $cultivos = $query->get()->map(fn ($c) => [
            'id'       => $c->id,
            'codigo'   => $c->codigo,
            'lote'     => $c->lote?->nombre,
            'variedad' => $c->variedad?->nombre,
        ]);

        return response()->json([
            'data' => $cultivos,
        ]);
    }
}
