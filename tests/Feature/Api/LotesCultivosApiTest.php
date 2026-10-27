<?php

namespace Tests\Feature\Api;

use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LotesCultivosApiTest extends TestCase
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
            'nombre'   => 'Admin Test',
            'email'    => 'admin_test@sigvos.test',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_lotes_via_api(): void
    {
        Lote::create([
            'identificador'  => 'A',
            'nombre'         => 'Lote Alpha',
            'ubicacion'      => 'Sector Norte',
            'area_ha'        => 2.5,
            'estado'         => 'disponible',
            'activo'         => true,
            'es_alternativo' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/lotes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'conteo_estados' => ['total', 'disponible', 'ocupado', 'en_descanso', 'inactivo'],
                'data' => [
                    'data' => [
                        '*' => ['id', 'identificador', 'nombre', 'ubicacion', 'area_ha', 'estado'],
                    ],
                ],
            ]);
    }

    public function test_admin_can_create_lote(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/lotes', [
                'identificador' => 'B',
                'nombre'        => 'Lote Beta',
                'ubicacion'     => 'Sector Sur',
                'area_ha'       => 3.2,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.identificador', 'B')
            ->assertJsonPath('data.nombre', 'Lote Beta');

        $this->assertDatabaseHas('lotes', [
            'identificador' => 'B',
            'nombre'        => 'Lote Beta',
        ]);
    }

    public function test_can_list_cultivos_and_catalogs(): void
    {
        $catResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/catalogos/tipos-cultivo');

        $catResponse->assertStatus(200)
            ->assertJsonStructure(['data']);

        $cultivosResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/cultivos');

        $cultivosResponse->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
