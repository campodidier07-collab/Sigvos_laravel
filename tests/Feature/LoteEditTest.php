<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoteEditTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $worker;
    protected Lote $lote;

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

        $this->lote = Lote::create([
            'identificador'     => 'K',
            'nombre'            => 'Lote Las Palmas',
            'ubicacion'         => 'Sector Sur Parcela 10',
            'area_ha'           => 3.50,
            'id_tipo_preferido' => 1,
            'estado'            => 'disponible',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);
    }

    /**
     * Criterio 1: Modificación exitosa de información básica
     * Cuando edito el nombre, ubicación o área del lote -> Entonces el sistema guarda la información y muestra confirmación.
     */
    public function test_criterio_1_modificacion_exitosa_de_informacion_basica(): void
    {
        $response = $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador'     => 'K',
            'nombre'            => 'Lote Las Palmas Modificado',
            'ubicacion'         => 'Sector Sur Renovado Parcela 12',
            'area_ha'           => 4.25,
            'id_tipo_preferido' => 1,
        ]);

        $response->assertRedirect(route('lotes.show', $this->lote));
        $response->assertSessionHas('success', 'Lote actualizado exitosamente.');

        $this->lote->refresh();
        $this->assertEquals('Lote Las Palmas Modificado', $this->lote->nombre);
        $this->assertEquals('Sector Sur Renovado Parcela 12', $this->lote->ubicacion);
        $this->assertEquals('4.25', (string)$this->lote->area_ha);
    }

    /**
     * Criterio 2: Restricción de modificación de identificador
     * Cuando intento modificar el identificador del lote -> Entonces el sistema bloquea ese campo y muestra que no puede ser editado.
     */
    public function test_criterio_2_restriccion_de_modificacion_de_identificador(): void
    {
        // 1. Verificación en la vista de edición: campo readonly y badge de bloqueo
        $viewResponse = $this->actingAs($this->admin)->get(route('lotes.edit', $this->lote));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('readonly', false);
        $viewResponse->assertSee('Bloqueado');
        $viewResponse->assertSee('El identificador único no puede ser modificado');

        // 2. Verificación en el backend: intento malicioso de alterar el identificador
        $response = $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador' => 'Z', // Intento de cambiar de K a Z
            'nombre'        => $this->lote->nombre,
            'ubicacion'     => $this->lote->ubicacion,
            'area_ha'       => $this->lote->area_ha,
        ]);

        $response->assertSessionHasErrors('identificador');
        $this->lote->refresh();
        $this->assertEquals('K', $this->lote->identificador);
    }

    /**
     * Criterio 3: Modificación de ubicación
     * Cuando modifico la ubicación del lote -> Entonces el sistema actualiza la ubicación y refleja el cambio guardado.
     */
    public function test_criterio_3_modificacion_de_ubicacion(): void
    {
        $nuevaUbicacion = 'Zona Alta - Lado Occidental km 5';

        $response = $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador' => 'K',
            'nombre'        => $this->lote->nombre,
            'ubicacion'     => $nuevaUbicacion,
            'area_ha'       => $this->lote->area_ha,
        ]);

        $response->assertRedirect(route('lotes.show', $this->lote));
        $this->assertDatabaseHas('lotes', [
            'id'        => $this->lote->id,
            'ubicacion' => $nuevaUbicacion,
        ]);

        // Verificar reflejo en la vista de detalle
        $showResponse = $this->actingAs($this->admin)->get(route('lotes.show', $this->lote));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($nuevaUbicacion);
    }

    /**
     * Criterio 4: Validación de área mínima al modificar
     * Cuando ingreso un área inferior al mínimo permitido -> Entonces el sistema muestra un error y no permite guardar.
     */
    public function test_criterio_4_validacion_de_area_minima_al_modificar(): void
    {
        // Caso A: Área cero
        $responseZero = $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador' => 'K',
            'nombre'        => $this->lote->nombre,
            'ubicacion'     => $this->lote->ubicacion,
            'area_ha'       => 0,
        ]);
        $responseZero->assertSessionHasErrors('area_ha');

        // Caso B: Área negativa
        $responseNegative = $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador' => 'K',
            'nombre'        => $this->lote->nombre,
            'ubicacion'     => $this->lote->ubicacion,
            'area_ha'       => -2.5,
        ]);
        $responseNegative->assertSessionHasErrors('area_ha');

        // El área del lote se mantiene intacta
        $this->lote->refresh();
        $this->assertEquals('3.50', (string)$this->lote->area_ha);
    }

    /**
     * Criterio 5: Restricción de modificación con cultivo activo
     * Cuando intento cambiar datos sensibles del lote con cultivo activo -> Entonces el sistema muestra una advertencia y limita la modificación.
     */
    public function test_criterio_5_restriccion_de_modificacion_con_cultivo_activo(): void
    {
        // Crear un cultivo activo asociado al lote
        Cultivo::create([
            'codigo'                  => 'CULT-2026-TEST',
            'id_lote'                 => $this->lote->id,
            'id_variedad'             => 1,
            'fecha_siembra'           => now()->subDays(10),
            'fecha_cosecha_estimada'  => now()->addDays(90),
            'estado'                  => 'en_crecimiento',
            'activo_en_lote'          => true,
            'registrado_por'          => $this->admin->id,
        ]);

        $this->lote->update(['estado' => 'ocupado']);

        // 1. Verificación en la vista de edición: advertencia y protección del área
        $viewResponse = $this->actingAs($this->admin)->get(route('lotes.edit', $this->lote));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Lote con Cultivo Activo (CULT-2026-TEST)');
        $viewResponse->assertSee('Modificación limitada por cultivo activo');

        // 2. Verificación en backend: intento de alterar el área sensible
        $response = $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador' => 'K',
            'nombre'        => 'Nuevo Nombre Permitido',
            'ubicacion'     => $this->lote->ubicacion,
            'area_ha'       => 1.00, // Intento de cambiar área de 3.5 a 1.0 con cultivo activo
        ]);

        $response->assertSessionHasErrors('area_ha');
        $this->lote->refresh();
        $this->assertEquals('3.50', (string)$this->lote->area_ha);
    }

    /**
     * Criterio 6: Historial de modificaciones del lote
     * Cuando consulto el historial del lote -> Entonces el sistema muestra el registro de modificaciones realizadas.
     */
    public function test_criterio_6_historial_de_modificaciones_del_lote(): void
    {
        // Modificar el lote como admin
        $this->actingAs($this->admin)->put(route('lotes.update', $this->lote), [
            'identificador' => 'K',
            'nombre'        => 'Lote El Roble Reauditado',
            'ubicacion'     => 'Nueva Ubicación Auditada',
            'area_ha'       => 5.00,
        ]);

        // Verificar que se creó el registro en la tabla auditoria
        $this->assertDatabaseHas('auditoria', [
            'tabla'         => 'lotes',
            'id_registro'   => (string)$this->lote->id,
            'accion'        => 'UPDATE',
            'realizado_por' => $this->admin->id,
        ]);

        // Verificar que en la vista de detalle se muestra el registro de modificaciones
        $showResponse = $this->actingAs($this->admin)->get(route('lotes.show', $this->lote));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Historial de Modificaciones');
        $showResponse->assertSee('Registro de Modificaciones del Lote');
        $showResponse->assertSee('Lote El Roble Reauditado');
        $showResponse->assertSee('Nueva Ubicación Auditada');
    }

    /**
     * Criterio 7: Cancelación de edición sin guardar
     * Cuando hago clic en "Cancelar" -> Entonces el sistema no guarda los cambios y restaura la información anterior.
     */
    public function test_criterio_7_cancelacion_de_edicion_sin_guardar(): void
    {
        // 1. Verificar presencia del botón Cancelar en la vista de edición con la ruta al detalle del lote
        $viewResponse = $this->actingAs($this->admin)->get(route('lotes.edit', $this->lote));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Cancelar');
        $viewResponse->assertSee(route('lotes.show', $this->lote));

        // 2. Simular flujo del usuario: visita la edición y navega de regreso a la ruta de cancelación
        $cancelResponse = $this->actingAs($this->admin)->get(route('lotes.show', $this->lote));
        $cancelResponse->assertStatus(200);

        // Los datos del lote se mantienen íntegros y no modificados
        $this->assertDatabaseHas('lotes', [
            'id'            => $this->lote->id,
            'nombre'        => 'Lote Las Palmas',
            'ubicacion'     => 'Sector Sur Parcela 10',
            'area_ha'       => 3.50,
            'identificador' => 'K',
        ]);
    }
}
