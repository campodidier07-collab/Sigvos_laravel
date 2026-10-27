<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoteTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $worker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1, // Admin
            'activo' => true,
        ]);

        $this->worker = Usuario::factory()->create([
            'id_rol' => 2, // Trabajador
            'activo' => true,
        ]);
    }

    /**
     * Escenario 1: Registro exitoso de nuevo lote
     */
    public function test_admin_can_create_lote_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post('/lotes', [
            'identificador' => 'X',
            'nombre'        => 'Lote Experimental',
            'ubicacion'     => 'Sector Este Parcela 4',
            'area_ha'       => 4.50,
        ]);

        $response->assertRedirect(route('lotes.index'));
        $this->assertDatabaseHas('lotes', [
            'identificador' => 'X',
            'nombre'        => 'Lote Experimental',
            'area_ha'       => 4.50,
            'estado'        => 'disponible',
            'activo'        => 1,
        ]);

        // Verificar que aparece en el listado
        $listResponse = $this->actingAs($this->admin)->get('/lotes');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Lote Experimental');
        $listResponse->assertSee('X');
    }

    /**
     * Escenario 2: Intento de duplicar identificador
     */
    public function test_cannot_create_lote_with_duplicate_identificador(): void
    {
        Lote::create([
            'identificador' => 'A',
            'nombre'        => 'Lote Original',
            'ubicacion'     => 'Zona Norte',
            'area_ha'       => 2.0,
            'estado'        => 'disponible',
            'activo'        => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/lotes', [
            'identificador' => 'A',
            'nombre'        => 'Lote Duplicado',
            'ubicacion'     => 'Zona Sur',
            'area_ha'       => 3.0,
        ]);

        $response->assertSessionHasErrors(['identificador']);
        $this->assertDatabaseMissing('lotes', [
            'nombre' => 'Lote Duplicado',
        ]);
    }

    /**
     * Escenario 3: Consultar estado de lotes
     */
    public function test_can_view_lotes_list_with_current_status(): void
    {
        $loteDisponible = Lote::create([
            'identificador' => 'M',
            'nombre'        => 'Lote Disponible',
            'ubicacion'     => 'Zona Central',
            'area_ha'       => 1.5,
            'estado'        => 'disponible',
            'activo'        => true,
        ]);

        $loteOcupado = Lote::create([
            'identificador' => 'N',
            'nombre'        => 'Lote Ocupado',
            'ubicacion'     => 'Zona Poniente',
            'area_ha'       => 3.0,
            'estado'        => 'ocupado',
            'activo'        => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/lotes');

        $response->assertStatus(200);
        $response->assertSee('Lote Disponible');
        $response->assertSee('Disponible');
        $response->assertSee('Lote Ocupado');
        $response->assertSee('Ocupado');
    }

    /**
     * Escenario 4: Validación de campos obligatorios
     */
    public function test_cannot_create_lote_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/lotes', [
            'identificador' => '',
            'nombre'        => '',
            'ubicacion'     => '',
            'area_ha'       => '',
        ]);

        $response->assertSessionHasErrors(['identificador', 'nombre', 'ubicacion', 'area_ha']);
    }

    /**
     * Seguridad: Trabajador no puede crear lotes
     */
    public function test_worker_cannot_create_lote(): void
    {
        $response = $this->actingAs($this->worker)->post('/lotes', [
            'identificador' => 'Z',
            'nombre'        => 'Lote Prohibido',
            'ubicacion'     => 'Zona Sur',
            'area_ha'       => 1.0,
        ]);

        $response->assertStatus(403);
    }
}
