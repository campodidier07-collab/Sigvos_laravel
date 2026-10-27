<?php

namespace Tests\Feature\Api;

use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_user_can_login_via_api_and_receives_token(): void
    {
        Usuario::create([
            'id_rol'   => 1,
            'nombre'   => 'Admin Test',
            'email'    => 'admin@correo.com',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'       => 'admin@correo.com',
            'password'    => 'password123',
            'device_name' => 'flutter-test',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ]);

        $token = $response->json('token');
        $this->assertNotEmpty($token);

        // Probar /api/auth/me con el token
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'admin@correo.com');

        // Probar /api/auth/logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        // Limpiar guard en memoria del proceso de testing para forzar re-autenticación
        $this->app['auth']->forgetGuards();

        // Verificar que el token revocado ya no funciona
        $unauthResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $unauthResponse->assertStatus(401);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        Usuario::create([
            'id_rol'   => 1,
            'nombre'   => 'Admin Test',
            'email'    => 'admin@correo.com',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'admin@correo.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_register_via_api(): void
    {
        $testEmail = 'flutter_tester@sigvos.test';

        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Flutter Tester',
            'email'                 => $testEmail,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'device_name'           => 'flutter-device',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ]);

        $this->assertDatabaseHas('usuarios', [
            'email'  => $testEmail,
            'id_rol' => 2,
        ]);
    }
}
