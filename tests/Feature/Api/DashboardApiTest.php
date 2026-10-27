<?php

namespace Tests\Feature\Api;

use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_guest_cannot_access_dashboard_api(): void
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_dashboard_data(): void
    {
        $usuario = Usuario::create([
            'id_rol'   => 1, // Administrador
            'nombre'   => 'Admin Test',
            'email'    => 'admin_dash@sigvos.test',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $token = $usuario->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'usuario' => ['id', 'name', 'role', 'es_admin'],
                'estadisticas' => [
                    'total_lotes',
                    'cultivos_activos',
                    'actividades_pendientes',
                    'actividades_completadas',
                    'total_usuarios',
                    'total_trabajadores',
                ],
                'actividades_proximas',
                'cosechas_proximas',
                'notificaciones_recientes',
            ]);
    }
}
