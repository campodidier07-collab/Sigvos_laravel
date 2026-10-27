<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_worker_can_access_dashboard_with_worker_panel(): void
    {
        $worker = Usuario::factory()->create([
            'id_rol' => 2, // 2 = Trabajador
            'activo' => true,
        ]);

        $response = $this->actingAs($worker)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Mi Panel');
        $response->assertSee('Tus tareas y cultivos asignados');
    }

    public function test_worker_cannot_access_admin_usuarios_route(): void
    {
        $worker = Usuario::factory()->create([
            'id_rol' => 2,
            'activo' => true,
        ]);

        $response = $this->actingAs($worker)->get('/usuarios');

        $response->assertStatus(403);
    }

    public function test_worker_cannot_access_admin_reportes_route(): void
    {
        $worker = Usuario::factory()->create([
            'id_rol' => 2,
            'activo' => true,
        ]);

        $response = $this->actingAs($worker)->get('/reportes');

        $response->assertStatus(403);
    }

    public function test_worker_cannot_access_admin_asignaciones_route(): void
    {
        $worker = Usuario::factory()->create([
            'id_rol' => 2,
            'activo' => true,
        ]);

        $response = $this->actingAs($worker)->get('/asignaciones');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_asignaciones(): void
    {
        $admin = Usuario::factory()->create([
            'id_rol' => 1, // 1 = Administrador
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get('/asignaciones');

        $response->assertStatus(200);
    }

    public function test_worker_is_logged_out_if_bloqueado_hasta_is_future(): void
    {
        $worker = Usuario::factory()->create([
            'id_rol' => 2,
            'activo' => true,
            'bloqueado_hasta' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($worker)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
