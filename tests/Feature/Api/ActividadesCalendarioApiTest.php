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

class ActividadesCalendarioApiTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected string $adminToken;
    protected Cultivo $cultivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::create([
            'id_rol'   => 1,
            'nombre'   => 'Admin Test',
            'email'    => 'admin_act@sigvos.test',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;

        $lote = Lote::create([
            'identificador' => 'X',
            'nombre'        => 'Lote X',
            'ubicacion'     => 'Sector Central',
            'area_ha'       => 1.0,
            'estado'        => 'ocupado',
            'activo'        => true,
        ]);

        $variedad = Variedad::first();

        $this->cultivo = Cultivo::create([
            'id_lote'                => $lote->id,
            'id_variedad'            => $variedad->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-X-TEST',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => now(),
            'fecha_cosecha_estimada' => now()->addDays(90),
            'activo_en_lote'         => 1,
        ]);
    }

    public function test_can_create_and_list_actividad_via_api(): void
    {
        $tipo = TipoActividad::first();

        // 1. Crear actividad
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/actividades', [
                'id_cultivo'        => $this->cultivo->id,
                'id_tipo_actividad' => $tipo->id,
                'fecha_programada'  => now()->addDays(2)->format('Y-m-d'),
                'descripcion'       => 'Riego de emergencia',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.descripcion', 'Riego de emergencia')
            ->assertJsonPath('data.estado', 'pendiente');

        $actividadId = $createResponse->json('data.id');

        // 2. Listar actividades
        $listResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/actividades');

        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'conteos' => ['total', 'pendientes', 'completadas', 'canceladas'],
                'data' => [
                    'data' => [
                        '*' => ['id', 'descripcion', 'estado', 'fecha_programada'],
                    ],
                ],
            ]);

        // 3. Marcar como completada
        $completeResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson("/api/actividades/{$actividadId}/completar", [
                'observaciones' => 'Labor ejecutada sin novedades',
            ]);

        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.estado', 'completada');

        $this->assertDatabaseHas('actividades', [
            'id'     => $actividadId,
            'estado' => 'completada',
        ]);
    }

    public function test_can_get_calendario_eventos_via_api(): void
    {
        $tipo = TipoActividad::first();

        Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $tipo->id,
            'creado_por'        => $this->admin->id,
            'asignado_a'        => $this->admin->id,
            'fecha_programada'  => now()->format('Y-m-d'),
            'descripcion'       => 'Poda programada',
            'estado'            => 'pendiente',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/calendario/eventos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'tipo', 'titulo', 'fecha', 'estado', 'color'],
                ],
            ]);
    }
}
