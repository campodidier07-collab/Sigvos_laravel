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
}
