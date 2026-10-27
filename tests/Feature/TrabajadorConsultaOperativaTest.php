<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\AsignacionLote;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Usuario;
use App\Models\Variedad;
use Carbon\Carbon;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrabajadorConsultaOperativaTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $trabajador1;
    protected Usuario $trabajador2;
    protected Lote $loteAsignado;
    protected Lote $loteAjeno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->trabajador1 = Usuario::factory()->create([
            'nombre' => 'Juan Trabajador',
            'email'  => 'juan@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->trabajador2 = Usuario::factory()->create([
            'nombre' => 'Pedro Trabajador',
            'email'  => 'pedro@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->loteAsignado = Lote::create([
            'identificador'     => 'A',
            'nombre'            => 'Lote Las Palmas',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 3.50,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $this->loteAjeno = Lote::create([
            'identificador'     => 'B',
            'nombre'            => 'Lote San Rafael',
            'ubicacion'         => 'Sector Sur',
            'area_ha'           => 2.80,
            'id_tipo_preferido' => 2,
            'estado'            => 'disponible',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        // Asignar lote A a trabajador 1
        AsignacionLote::create([
            'id_usuario'   => $this->trabajador1->id,
            'id_lote'      => $this->loteAsignado->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteAsignado->id}_user_{$this->trabajador1->id}",
            'creado_en'    => now(),
        ]);

        // Asignar lote B a trabajador 2
        AsignacionLote::create([
            'id_usuario'   => $this->trabajador2->id,
            'id_lote'      => $this->loteAjeno->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteAjeno->id}_user_{$this->trabajador2->id}",
            'creado_en'    => now(),
        ]);
    }

    /**
     * Criterio 1: Visualización de lotes asignados
     * Dado que tengo lotes asignados por el administrador,
     * cuando ingreso al módulo de consulta operativa,
     * entonces el sistema debe mostrar únicamente la información que me corresponde.
     */
    public function test_criterio_1_visualizacion_de_lotes_asignados(): void
    {
        $response = $this->actingAs($this->trabajador1)
            ->get(route('lotes.index'));

        $response->assertOk();
        // Ve su lote asignado
        $response->assertSee('Lote Las Palmas');
        // NO ve el lote de otro trabajador
        $response->assertDontSee('Lote San Rafael');
    }

    /**
     * Criterio 2: Detalle del cultivo activo por lote
     * Dado que selecciono uno de mis lotes asignados,
     * cuando abro su detalle,
     * entonces el sistema debe mostrar la información del cultivo activo.
     */
    public function test_criterio_2_detalle_del_cultivo_activo_por_lote(): void
    {
        $variedad = Variedad::first();
        $cultivo = Cultivo::create([
            'id_lote'                => $this->loteAsignado->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-TEST-001',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(120),
            'activo_en_lote'         => $this->loteAsignado->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->trabajador1)
            ->get(route('lotes.show', $this->loteAsignado));

        $response->assertOk();
        $response->assertSee('CUL-TEST-001');
        $response->assertSee($variedad->nombre);
        $response->assertSee('Sembrado');
        $response->assertSee(Carbon::today()->format('d/m/Y'));
    }

    /**
     * Criterio 3: Consulta de actividades pendientes del día
     * Dado que tengo actividades programadas en mis cultivos,
     * cuando reviso mi agenda diaria,
     * entonces el sistema debe mostrar las tareas pendientes con su información principal.
     */
    public function test_criterio_3_consulta_de_actividades_pendientes_del_dia(): void
    {
        $variedad = Variedad::first();
        $cultivo = Cultivo::create([
            'id_lote'                => $this->loteAsignado->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-ACT-002',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => $this->loteAsignado->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $tipoActividad = TipoActividad::first();

        // Actividad programada para HOY
        $actividadHoy = Actividad::create([
            'id_cultivo'        => $cultivo->id,
            'id_tipo_actividad' => $tipoActividad->id,
            'descripcion'       => 'Riego matutino en Lote A',
            'estado'            => 'pendiente',
            'fecha_programada'  => Carbon::today(),
            'asignado_a'        => $this->trabajador1->id,
            'creado_por'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->trabajador1)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Actividades de hoy');
        $response->assertSee($tipoActividad->nombre);
        $response->assertSee('CUL-ACT-002');
        $response->assertSee('Lote Las Palmas');
    }

    /**
     * Criterio 4: Bloqueo de acceso a lotes no asignados
     * Dado que existen lotes asignados a otros trabajadores,
     * cuando intento consultar uno que no me corresponde,
     * entonces el sistema debe restringir el acceso y mostrar que no tengo permisos (403).
     */
    public function test_criterio_4_bloqueo_de_acceso_a_lotes_no_asignados(): void
    {
        // 4a. Intento acceder al lote de otro trabajador
        $responseLote = $this->actingAs($this->trabajador1)
            ->get(route('lotes.show', $this->loteAjeno));

        $responseLote->assertForbidden();

        // 4b. Intento acceder al cultivo sembrado en lote de otro trabajador
        $variedad = Variedad::first();
        $cultivoAjeno = Cultivo::create([
            'id_lote'                => $this->loteAjeno->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-AJENO-999',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(90),
            'activo_en_lote'         => $this->loteAjeno->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $responseCultivo = $this->actingAs($this->trabajador1)
            ->get(route('cultivos.show', $cultivoAjeno));

        $responseCultivo->assertForbidden();
    }

    /**
     * Criterio 5: Sin asignaciones activas
     * Dado que no tengo lotes activos asignados,
     * cuando ingreso a mi panel operativo,
     * entonces el sistema debe informarlo claramente.
     */
    public function test_criterio_5_sin_asignaciones_activas(): void
    {
        $trabajadorNuevo = Usuario::factory()->create([
            'nombre' => 'Ana Nueva Trabajadora',
            'id_rol' => 2,
            'activo' => true,
        ]);

        // Consulta del dashboard del trabajador sin lotes
        $responseDashboard = $this->actingAs($trabajadorNuevo)
            ->get(route('dashboard'));

        $responseDashboard->assertOk();
        $responseDashboard->assertSee('Sin asignaciones activas');
        $responseDashboard->assertSee('0 lote(s) asignado(s)');

        // Consulta de lotes.index sin lotes
        $responseIndex = $this->actingAs($trabajadorNuevo)
            ->get(route('lotes.index'));

        $responseIndex->assertOk();
        $responseIndex->assertSee('Sin asignaciones activas');
        $responseIndex->assertSee('Actualmente no tienes lotes activos asignados');
    }

    /**
     * Criterio 6: Consulta en modo offline
     * Dado que me encuentro sin conexión,
     * cuando abro el módulo de mis lotes y cultivos,
     * entonces el sistema debe permitir la consulta de la información disponible en caché local.
     */
    public function test_criterio_6_consulta_en_modo_offline(): void
    {
        // 6a. Verificación del endpoint JSON consumido por el motor offline
        $responseApi = $this->actingAs($this->trabajador1)
            ->getJson(route('dashboard.data'));

        $responseApi->assertOk();
        $responseApi->assertJsonStructure([
            'estadisticas' => [
                'total_lotes',
                'cultivos_activos',
                'actividades_pendientes',
                'actividades_completadas',
            ],
            'actividadesProximas',
            'actividadesHoy',
            'cosechasProximas',
            'lotesAsignados',
            'timestamp',
        ]);

        // Las estadísticas están acotadas a su lote asignado
        $this->assertEquals(1, $responseApi->json('estadisticas.total_lotes'));

        // 6b. Verificación de elementos UI para modo offline en dashboard
        $responseView = $this->actingAs($this->trabajador1)
            ->get(route('dashboard'));

        $responseView->assertOk();
        $responseView->assertSee('offlineWorkerBanner');
        $responseView->assertSee('Modo Offline activado');
        $responseView->assertSee('sigvos_dashboard_worker_cache');
    }
}
