<?php

namespace Tests\Feature\Api;

use App\Models\Notificacion;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificacionesUsuariosApiTest extends TestCase
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
            'id_rol'   => 1, // Admin
            'nombre'   => 'Admin General',
            'email'    => 'admin_nu@sigvos.test',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_and_mark_notifications(): void
    {
        // 1. Crear 2 notificaciones para el admin
        $n1 = Notificacion::create([
            'id_usuario' => $this->admin->id,
            'titulo'     => 'Alerta de riego',
            'mensaje'    => 'Programar riego en lote 1',
            'tipo'       => 'alerta',
            'prioridad'  => 'alta',
            'leida'      => false,
        ]);

        $n2 = Notificacion::create([
            'id_usuario' => $this->admin->id,
            'titulo'     => 'Cosecha lista',
            'mensaje'    => 'Cultivo en lote 2 listo para cosechar',
            'tipo'       => 'info',
            'prioridad'  => 'media',
            'leida'      => false,
        ]);

        // 2. Listar notificaciones
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/notificaciones');

        $response->assertStatus(200)
            ->assertJsonPath('no_leidas', 2)
            ->assertJsonCount(2, 'data');

        // 3. Marcar una como leída
        $markOne = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson("/api/notificaciones/{$n1->id}/leida");

        $markOne->assertStatus(200);
        $this->assertTrue($n1->fresh()->leida);

        // 4. Marcar todas como leídas
        $markAll = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/notificaciones/marcar-todas');

        $markAll->assertStatus(200);
        $this->assertTrue($n2->fresh()->leida);

        // 5. Verificar que no_leidas sea 0
        $finalCheck = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/notificaciones');
        $finalCheck->assertJsonPath('no_leidas', 0);
    }

    public function test_user_management_crud_via_api(): void
    {
        // 1. Listar roles
        $rolesResp = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/roles');
        $rolesResp->assertStatus(200)->assertJsonStructure(['data']);

        // 2. Crear nuevo usuario / trabajador
        $createResp = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/usuarios', [
                'nombre'   => 'Carlos Operario',
                'email'    => 'carlos@sigvos.test',
                'telefono' => '3001234567',
                'id_rol'   => 2, // Trabajador
                'password' => 'secret123',
            ]);

        $createResp->assertStatus(201)
            ->assertJsonPath('usuario.nombre', 'Carlos Operario');

        $userId = $createResp->json('usuario.id');

        // 3. Ver detalle del usuario
        $showResp = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/usuarios/{$userId}");

        $showResp->assertStatus(200)
            ->assertJsonPath('usuario.email', 'carlos@sigvos.test');

        // 4. Actualizar usuario
        $updateResp = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/usuarios/{$userId}", [
                'nombre'   => 'Carlos Gomez Operario',
                'email'    => 'carlos@sigvos.test',
                'telefono' => '3009998877',
                'id_rol'   => 2,
            ]);

        $updateResp->assertStatus(200)
            ->assertJsonPath('usuario.nombre', 'Carlos Gomez Operario');

        // 5. Intentar que el admin se desactive a sí mismo (debe bloquearse)
        $selfDeact = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/usuarios/{$this->admin->id}", [
                'nombre' => $this->admin->nombre,
                'email'  => $this->admin->email,
                'id_rol' => 1,
                'activo' => false,
            ]);
        $selfDeact->assertStatus(422);

        // 6. Eliminar usuario creado
        $delResp = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/usuarios/{$userId}");
        $delResp->assertStatus(200);

        $this->assertDatabaseMissing('usuarios', ['id' => $userId]);
    }
}
