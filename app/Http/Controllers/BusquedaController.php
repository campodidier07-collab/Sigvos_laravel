<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    /**
     * Vista principal de Búsqueda Global y Filtros Avanzados (HU DP-9).
     */
    public function index(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $idLote = $request->input('id_lote');
        $estadoCultivo = $request->input('estado');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $tipoEntidad = $request->input('tipo', 'todos'); // todos, cultivos, actividades, lotes

        $lotes = Lote::where('activo', true)->orderBy('identificador')->get();
        $estadosCultivo = ['sembrado', 'creciendo', 'cosechado', 'perdido'];

        $cultivos = collect();
        $actividades = collect();
        $lotesResultado = collect();

        $haBuscado = $request->hasAny(['q', 'id_lote', 'estado', 'fecha_desde', 'fecha_hasta', 'tipo']);

        if ($haBuscado) {
            // ── 1. Búsqueda y filtrado de Cultivos ───────────────────────────
            if (in_array($tipoEntidad, ['todos', 'cultivos'])) {
                $queryCultivos = Cultivo::with(['lote', 'variedad.tipoCultivo']);

                // Criterio 1: Búsqueda rápida por nombre de cultivo o código
                if (!empty($q)) {
                    $queryCultivos->where(function ($sub) use ($q) {
                        $sub->where('codigo', 'like', "%{$q}%")
                            ->orWhereHas('variedad', function ($v) use ($q) {
                                $v->where('nombre', 'like', "%{$q}%")
                                  ->orWhereHas('tipoCultivo', fn ($tc) => $tc->where('nombre', 'like', "%{$q}%"));
                            })
                            ->orWhereHas('lote', function ($l) use ($q) {
                                $l->where('nombre', 'like', "%{$q}%")
                                  ->orWhere('identificador', 'like', "%{$q}%");
                            });
                    });
                }

                // Criterio 3: Filtro por lote específico
                if (!empty($idLote)) {
                    $queryCultivos->where('id_lote', $idLote);
                }

                // Criterio 5: Filtro por estado de cultivo
                if (!empty($estadoCultivo)) {
                    $queryCultivos->where('estado', $estadoCultivo);
                }

                // Criterio 2: Búsqueda por rango de fechas
                if (!empty($fechaDesde)) {
                    $queryCultivos->where('fecha_siembra', '>=', $fechaDesde);
                }
                if (!empty($fechaHasta)) {
                    $queryCultivos->where('fecha_siembra', '<=', $fechaHasta);
                }

                $cultivos = $queryCultivos->orderByDesc('fecha_siembra')->get();
            }

            // ── 2. Búsqueda y filtrado de Actividades ────────────────────────
            if (in_array($tipoEntidad, ['todos', 'actividades'])) {
                $queryActividades = Actividad::with(['cultivo.lote', 'cultivo.variedad', 'tipoActividad', 'asignadoA']);

                if (!empty($q)) {
                    $queryActividades->where(function ($sub) use ($q) {
                        $sub->where('descripcion', 'like', "%{$q}%")
                            ->orWhereHas('tipoActividad', fn ($ta) => $ta->where('nombre', 'like', "%{$q}%"))
                            ->orWhereHas('cultivo', function ($c) use ($q) {
                                $c->where('codigo', 'like', "%{$q}%")
                                  ->orWhereHas('variedad', fn ($v) => $v->where('nombre', 'like', "%{$q}%"));
                            });
                    });
                }

                if (!empty($idLote)) {
                    $queryActividades->whereHas('cultivo', fn ($c) => $c->where('id_lote', $idLote));
                }

                // Criterio 2: Rango de fechas aplicado a fecha_programada de actividades
                if (!empty($fechaDesde)) {
                    $queryActividades->where('fecha_programada', '>=', $fechaDesde);
                }
                if (!empty($fechaHasta)) {
                    $queryActividades->where('fecha_programada', '<=', $fechaHasta);
                }

                $actividades = $queryActividades->orderBy('fecha_programada')->get();
            }

            // ── 3. Búsqueda de Lotes ─────────────────────────────────────────
            if (in_array($tipoEntidad, ['todos', 'lotes'])) {
                $queryLotes = Lote::with(['tipoPreferido', 'cultivoActivo.variedad']);

                if (!empty($q)) {
                    $queryLotes->where(function ($sub) use ($q) {
                        $sub->where('nombre', 'like', "%{$q}%")
                            ->orWhere('identificador', 'like', "%{$q}%")
                            ->orWhere('ubicacion', 'like', "%{$q}%");
                    });
                }

                if (!empty($idLote)) {
                    $queryLotes->where('id', $idLote);
                }

                $lotesResultado = $queryLotes->orderBy('identificador')->get();
            }
        }

        $totalResultados = $cultivos->count() + $actividades->count() + $lotesResultado->count();

        return view('busqueda.index', compact(
            'lotes',
            'estadosCultivo',
            'cultivos',
            'actividades',
            'lotesResultado',
            'haBuscado',
            'totalResultados',
            'q',
            'idLote',
            'estadoCultivo',
            'fechaDesde',
            'fechaHasta',
            'tipoEntidad'
        ));
    }

    /**
     * Endpoint API para búsqueda rápida (Omnibox en vivo).
     */
    public function rapida(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $idLote = $request->input('id_lote');
        $estado = $request->input('estado');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        if (empty($q) && empty($idLote) && empty($estado) && empty($fechaDesde) && empty($fechaHasta)) {
            return response()->json([
                'cultivos'    => [],
                'actividades' => [],
                'lotes'       => [],
                'total'       => 0,
            ]);
        }

        // 1. Cultivos
        $cultivosQuery = Cultivo::with(['lote', 'variedad.tipoCultivo']);

        if (!empty($q)) {
            $cultivosQuery->where(function ($sub) use ($q) {
                $sub->where('codigo', 'like', "%{$q}%")
                    ->orWhereHas('variedad', function ($v) use ($q) {
                        $v->where('nombre', 'like', "%{$q}%")
                          ->orWhereHas('tipoCultivo', fn ($tc) => $tc->where('nombre', 'like', "%{$q}%"));
                    });
            });
        }
        if (!empty($idLote)) {
            $cultivosQuery->where('id_lote', $idLote);
        }
        if (!empty($estado)) {
            $cultivosQuery->where('estado', $estado);
        }
        if (!empty($fechaDesde)) {
            $cultivosQuery->where('fecha_siembra', '>=', $fechaDesde);
        }
        if (!empty($fechaHasta)) {
            $cultivosQuery->where('fecha_siembra', '<=', $fechaHasta);
        }

        $cultivos = $cultivosQuery->limit(8)->get()->map(fn ($c) => [
            'id'             => $c->id,
            'codigo'         => $c->codigo,
            'variedad'       => $c->variedad?->nombre ?? 'N/A',
            'tipo_cultivo'   => $c->variedad?->tipoCultivo?->nombre ?? 'N/A',
            'lote'           => $c->lote ? "{$c->lote->identificador} - {$c->lote->nombre}" : 'Sin lote',
            'estado'         => $c->estado,
            'fecha_siembra'  => $c->fecha_siembra?->format('d/m/Y'),
            'fecha_cosecha'  => $c->fecha_cosecha_estimada?->format('d/m/Y'),
            'url'            => route('cultivos.show', $c),
        ]);

        // 2. Actividades
        $actividadesQuery = Actividad::with(['cultivo.lote', 'tipoActividad', 'asignadoA']);

        if (!empty($q)) {
            $actividadesQuery->where(function ($sub) use ($q) {
                $sub->where('descripcion', 'like', "%{$q}%")
                    ->orWhereHas('tipoActividad', fn ($ta) => $ta->where('nombre', 'like', "%{$q}%"))
                    ->orWhereHas('cultivo', fn ($c) => $c->where('codigo', 'like', "%{$q}%"));
            });
        }
        if (!empty($idLote)) {
            $actividadesQuery->whereHas('cultivo', fn ($c) => $c->where('id_lote', $idLote));
        }
        if (!empty($fechaDesde)) {
            $actividadesQuery->where('fecha_programada', '>=', $fechaDesde);
        }
        if (!empty($fechaHasta)) {
            $actividadesQuery->where('fecha_programada', '<=', $fechaHasta);
        }

        $actividades = $actividadesQuery->limit(8)->get()->map(fn ($a) => [
            'id'               => $a->id,
            'nombre'           => $a->tipoActividad?->nombre ?? 'Actividad',
            'descripcion'      => $a->descripcion,
            'cultivo'          => $a->cultivo?->codigo ?? '—',
            'lote'             => $a->cultivo?->lote ? "Lote {$a->cultivo->lote->identificador}" : '—',
            'fecha_programada' => $a->fecha_programada?->format('d/m/Y'),
            'estado'           => $a->estado,
            'asignado_a'       => $a->asignadoA?->nombre ?? 'Sin asignar',
            'url'              => route('actividades.show', $a),
        ]);

        // 3. Lotes
        $lotesQuery = Lote::with(['tipoPreferido', 'cultivoActivo.variedad']);

        if (!empty($q)) {
            $lotesQuery->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('identificador', 'like', "%{$q}%")
                    ->orWhere('ubicacion', 'like', "%{$q}%");
            });
        }
        if (!empty($idLote)) {
            $lotesQuery->where('id', $idLote);
        }

        $lotes = $lotesQuery->limit(5)->get()->map(fn ($l) => [
            'id'             => $l->id,
            'identificador'  => $l->identificador,
            'nombre'         => $l->nombre,
            'ubicacion'      => $l->ubicacion,
            'area_ha'        => (float)$l->area_ha,
            'estado'         => $l->estado,
            'cultivo_activo' => $l->cultivoActivo?->codigo,
            'url'            => route('lotes.show', $l),
        ]);

        $total = $cultivos->count() + $actividades->count() + $lotes->count();

        return response()->json([
            'cultivos'    => $cultivos,
            'actividades' => $actividades,
            'lotes'       => $lotes,
            'total'       => $total,
        ]);
    }
}
