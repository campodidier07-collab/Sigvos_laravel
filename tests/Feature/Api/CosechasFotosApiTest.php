<?php

namespace Tests\Feature\Api;

use App\Models\Cultivo;
use App\Models\FotoCultivo;
use App\Models\Lote;
use App\Models\Usuario;
use App\Models\Variedad;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CosechasFotosApiTest extends TestCase
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
            'email'    => 'admin_cos@sigvos.test',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;

        $lote = Lote::create([
            'identificador' => 'Y',
            'nombre'        => 'Lote Y',
            'ubicacion'     => 'Sector Este',
            'area_ha'       => 2.0,
            'estado'        => 'ocupado',
            'activo'        => true,
        ]);

        $variedad = Variedad::first();

        $this->cultivo = Cultivo::create([
            'id_lote'                => $lote->id,
            'id_variedad'            => $variedad->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-Y-TEST',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => now()->subDays(60),
            'fecha_cosecha_estimada' => now()->addDays(30),
            'activo_en_lote'         => 1,
        ]);
    }

    public function test_can_list_and_register_cosechas_via_api(): void
    {
        // 1. Listar cosechas
        $listResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/cosechas');

        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'resumen' => ['total_kg_cosechados', 'total_cosechas', 'cultivos_pendientes'],
                'cultivos_listos',
                'cosechas',
            ]);

        // 2. Registrar cosecha
        $storeResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/cosechas', [
                'id_cultivo'            => $this->cultivo->id,
                'fecha_cosecha_real'    => now()->format('Y-m-d'),
                'cantidad_cosechada_kg' => 1500.5,
                'observaciones'         => 'Rendimiento excelente',
            ]);

        $storeResponse->assertStatus(200);

        $this->assertDatabaseHas('cultivos', [
            'id'                    => $this->cultivo->id,
            'estado'                => 'cosechado',
            'cantidad_cosechada_kg' => 1500.5,
        ]);
    }

    public function test_can_upload_and_list_fotografias_via_api(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('evidencia.jpg', 600, 600);

        $uploadResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson("/api/cultivos/{$this->cultivo->id}/fotos", [
                'foto'        => $file,
                'descripcion' => 'Foto de control biológico',
            ]);

        $uploadResponse->assertStatus(201)
            ->assertJsonPath('data.descripcion', 'Foto de control biológico');

        $fotoId = $uploadResponse->json('data.id');

        $galeriaResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/fotografias');

        $galeriaResponse->assertStatus(200)
            ->assertJsonStructure(['data']);

        // Borrar foto
        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/fotos/{$fotoId}");

        $deleteResponse->assertStatus(200);
    }
}
