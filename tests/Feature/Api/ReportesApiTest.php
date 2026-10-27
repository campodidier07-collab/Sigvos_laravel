<?php

namespace Tests\Feature\Api;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Usuario;
use App\Models\Variedad;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportesApiTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::create([
            'id_rol'   => 1,
            'nombre'   => 'Admin Reportes',
            'email'    => 'admin_rep@sigvos.test',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;

        $lote = Lote::create([
            'identificador' => 'REP-1',
            'nombre'        => 'Lote Reportes',
            'ubicacion'     => 'Zona Norte',
            'area_ha'       => 5.0,
            'estado'        => 'ocupado',
            'activo'        => true,
        ]);

        $variedad = Variedad::first();

        $cultivo = Cultivo::create([
            'id_lote'                => $lote->id,
            'id_variedad'            => $variedad->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-REP-01',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => now()->subDays(90),
            'fecha_cosecha_estimada' => now()->subDays(10),
            'fecha_cosecha_real'     => now()->subDays(5),
            'cantidad_cosechada_kg'  => 4500.0,
            'activo_en_lote'         => 0,
        ]);

        $tipoAct = TipoActividad::first();

        Actividad::create([
            'id_cultivo'        => $cultivo->id,
            'id_tipo_actividad' => $tipoAct->id,
            'creado_por'        => $this->admin->id,
            'descripcion'       => 'Poda fitosanitaria',
            'fecha_programada'  => now()->subDays(10),
            'estado'            => 'completada',
        ]);
    }

    public function test_can_get_analytics_and_reports_via_api(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/reportes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'resumen' => [
                    'total_cultivos_historicos',
                    'total_kg_cosechados',
                    'tasa_cumplimiento_labores',
                    'porcentaje_uso_suelo',
                ],
                'cultivos_por_estado',
                'rendimiento_por_variedad',
                'labores_30_dias',
                'suelo_lotes',
            ]);

        $this->assertEquals(4500.0, $response->json('resumen.total_kg_cosechados'));
        $this->assertEquals(100.0, $response->json('resumen.tasa_cumplimiento_labores'));
    }
}
