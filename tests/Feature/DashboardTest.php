<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Notificacion;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1,
            'activo' => true,
        ]);
    }

    public function test_admin_can_view_dashboard_with_executive_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Administrador');
        $response->assertSee('Cultivos activos');
        $response->assertSee('Actividades pendientes');
        $response->assertSee('Lotes registrados');
    }

    public function test_dashboard_contains_quick_actions(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee(route('cultivos.create'));
        $response->assertSee(route('actividades.create'));
        $response->assertSee(route('cosecha.index'));
        $response->assertSee(route('lotes.create'));
    }

    public function test_dashboard_contains_clickable_navigation_to_details(): void
    {
        $lote = Lote::create([
            'identificador' => 'LOTE-TEST-01',
            'nombre'        => 'Lote Norte',
            'area_ha'       => 2.5,
            'ubicacion'     => 'Zona Alta',
            'activo'        => true,
        ]);

        $cultivo = Cultivo::create([
            'id_lote'              => $lote->id,
            'id_variedad'          => 1,
            'codigo'               => 'CUL-TEST-001',
            'estado'               => 'sembrado',
            'fecha_siembra'        => now()->subDays(10),
            'fecha_cosecha_estimada'=> now()->addDays(20),
            'activo_en_lote'       => 1,
            'registrado_por'       => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee(route('cultivo.show', $cultivo));
        $response->assertSee(route('lotes.show', $lote));
    }

    public function test_dashboard_data_endpoint_returns_json_for_realtime_and_offline(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/dashboard/data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'estadisticas' => [
                'total_lotes',
                'cultivos_activos',
                'actividades_pendientes',
                'actividades_completadas',
                'total_usuarios',
                'total_trabajadores',
            ],
            'actividadesProximas',
            'cosechasProximas',
            'notificaciones',
            'timestamp',
        ]);
    }

    public function test_dashboard_renders_system_alerts_and_indicators(): void
    {
        Notificacion::create([
            'id_usuario' => $this->admin->id,
            'titulo'     => 'Alerta de Riego',
            'mensaje'    => 'Verificar válvula en lote 3.',
            'tipo'       => 'alerta',
            'leida'      => false,
            'creado_en'  => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Alertas y Notificaciones del Sistema');
        $response->assertSee('Alerta de Riego');
        $response->assertSee('Verificar válvula en lote 3.');
        $response->assertSee('offlineBanner');
    }

    public function test_dashboard_metrics_update_dynamically_when_creating_lote_cultivo_actividad(): void
    {
        // 1. Initial counts: 0 lotes, 0 cultivos, 0 actividades
        $res1 = $this->actingAs($this->admin)->getJson('/dashboard/data');
        $res1->assertStatus(200);
        $this->assertEquals(0, $res1->json('estadisticas.total_lotes'));
        $this->assertEquals(0, $res1->json('estadisticas.cultivos_activos'));
        $this->assertEquals(0, $res1->json('estadisticas.actividades_pendientes'));

        // 2. Create a Lote
        $lote = Lote::create([
            'identificador' => 'X',
            'nombre'        => 'Lote X Experimental',
            'area_ha'       => 5.0,
            'ubicacion'     => 'Sector Sur',
            'activo'        => true,
            'estado'        => 'disponible',
        ]);

        $res2 = $this->actingAs($this->admin)->getJson('/dashboard/data');
        $this->assertEquals(1, $res2->json('estadisticas.total_lotes'));

        // 3. Create a Cultivo on Lote X
        $cultivo = Cultivo::create([
            'id_lote'                => $lote->id,
            'id_variedad'            => 1,
            'codigo'                 => 'CUL-X-01',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => now(),
            'fecha_cosecha_estimada' => now()->addDays(90),
            'activo_en_lote'         => $lote->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $res3 = $this->actingAs($this->admin)->getJson('/dashboard/data');
        $this->assertEquals(1, $res3->json('estadisticas.cultivos_activos'));

        // 4. Create an Actividad on Cultivo
        Actividad::create([
            'id_cultivo'        => $cultivo->id,
            'id_tipo_actividad' => 1,
            'asignado_a'        => $this->admin->id,
            'creado_por'        => $this->admin->id,
            'fecha_programada'  => now()->addDays(2),
            'descripcion'       => 'Fertilización con urea',
            'estado'            => 'pendiente',
        ]);

        $res4 = $this->actingAs($this->admin)->getJson('/dashboard/data');
        $this->assertEquals(1, $res4->json('estadisticas.actividades_pendientes'));
    }
}
