<?php

namespace Tests\Feature\Api;

use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->user = Usuario::create([
            'id_rol'   => 2, // Trabajador
            'nombre'   => 'Pedro Perez',
            'email'    => 'pedro@sigvos.test',
            'telefono' => '3115554433',
            'password' => Hash::make('password123'),
            'activo'   => true,
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_view_and_update_profile(): void
    {
        // 1. Ver perfil
        $showResp = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/perfil');

        $showResp->assertStatus(200)
            ->assertJsonPath('usuario.email', 'pedro@sigvos.test')
            ->assertJsonPath('usuario.rol', 'Trabajador');

        // 2. Actualizar datos de perfil
        $updateResp = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/perfil', [
                'nombre'   => 'Pedro Perez Gomez',
                'email'    => 'pedro_nuevo@sigvos.test',
                'telefono' => '3221110099',
            ]);

        $updateResp->assertStatus(200)
            ->assertJsonPath('usuario.nombre', 'Pedro Perez Gomez')
            ->assertJsonPath('usuario.email', 'pedro_nuevo@sigvos.test');

        $this->assertDatabaseHas('usuarios', [
            'id'    => $this->user->id,
            'email' => 'pedro_nuevo@sigvos.test',
        ]);
    }

    public function test_can_change_password(): void
    {
        // 1. Error con contraseña actual incorrecta
        $failResp = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/perfil/password', [
                'current_password'      => 'wrongpassword',
                'password'              => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ]);

        $failResp->assertStatus(422);

        // 2. Éxito con contraseña correcta
        $okResp = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/perfil/password', [
                'current_password'      => 'password123',
                'password'              => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ]);

        $okResp->assertStatus(200);

        $this->assertTrue(Hash::check('newsecret123', $this->user->fresh()->password));
    }
}
