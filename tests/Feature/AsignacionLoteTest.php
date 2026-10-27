<?php

namespace Tests\Feature;

use App\Models\AsignacionLote;
use App\Models\Lote;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionLoteTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $worker1;
    protected Usuario $worker2;
    protected Lote $loteA;
    protected Lote $loteB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1, // Admin
            'activo' => true,
        ]);

        $this->worker1 = Usuario::factory()->create([
            'nombre' => 'Carlos Trabajador',
            'email'  => 'carlos@sigvos.test',
            'id_rol' => 2, // Trabajador
            'activo' => true,
        ]);

        $this->worker2 = Usuario::factory()->create([
            'nombre' => 'María Trabajadora',
            'email'  => 'maria@sigvos.test',
            'id_rol' => 2, // Trabajador
            'activo' => true,
        ]);

        $this->loteA = Lote::create([
            'identificador'     => 'A',
            'nombre'            => 'Lote Los Pinos',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 4.50,
            'id_tipo_preferido' => 1,
            'estado'            => 'disponible',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $this->loteB = Lote::create([
            'identificador'     => 'B',
            'nombre'            => 'Lote El Manantial',
            'ubicacion'         => 'Sector Sur',
            'area_ha'           => 3.20,
            'id_tipo_preferido' => 2,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);
    }

    /**
     * Criterio 1: Asignación exitosa de lote a trabajador
     * Dado que existe un trabajador activo y un lote habilitado para operar,
     * cuando realizo la asignación, entonces el sistema debe registrarla correctamente.
     */
    public function test_criterio_1_asignacion_exitosa_de_lote_a_trabajador(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.store'), [
                'id_usuario' => $this->worker1->id,
                'id_lote'    => $this->loteA->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asignaciones_lote', [
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteA->id}_user_{$this->worker1->id}",
        ]);

        // Verificar que la relación en el modelo responde
        $this->assertTrue($this->worker1->lotes->contains('id', $this->loteA->id));
    }

    /**
     * Criterio 2: Evitar duplicidad de asignación activa
     * Dado que el trabajador ya tiene una asignación activa sobre ese mismo lote,
     * cuando intento registrarla nuevamente, entonces el sistema debe validarlo y rechazar la operación.
     */
    public function test_criterio_2_evitar_duplicidad_de_asignacion_activa(): void
    {
        // Primera asignación
        AsignacionLote::create([
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteA->id}_user_{$this->worker1->id}",
            'creado_en'    => now(),
        ]);

        // Intentar registrar la misma asignación de nuevo
        $response = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.store'), [
                'id_usuario' => $this->worker1->id,
                'id_lote'    => $this->loteA->id,
            ]);

        $response->assertSessionHasErrors(['id_usuario']);

        // Debe mantenerse únicamente 1 registro en la base de datos
        $this->assertEquals(1, AsignacionLote::where('id_lote', $this->loteA->id)
            ->where('id_usuario', $this->worker1->id)
            ->count());
    }

    /**
     * Criterio 3: Validar rol permitido para asignación
     * Dado que deseo asignar un lote a un usuario del sistema, cuando este no tiene el rol
     * de trabajador o se encuentra inactivo, entonces el sistema bloquea la asignación.
     */
    public function test_criterio_3_validar_rol_permitido_para_asignacion(): void
    {
        // 3a. Usuario con rol Administrador
        $otroAdmin = Usuario::factory()->create([
            'id_rol' => 1,
            'activo' => true,
        ]);

        $responseAdmin = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.store'), [
                'id_usuario' => $otroAdmin->id,
                'id_lote'    => $this->loteA->id,
            ]);

        $responseAdmin->assertSessionHasErrors(['id_usuario']);

        // 3b. Trabajador inactivo
        $trabajadorInactivo = Usuario::factory()->create([
            'id_rol' => 2,
            'activo' => false,
        ]);

        $responseInactivo = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.store'), [
                'id_usuario' => $trabajadorInactivo->id,
                'id_lote'    => $this->loteA->id,
            ]);

        $responseInactivo->assertSessionHasErrors(['id_usuario']);

        // 3c. Lote inactivo / no habilitado
        $loteInactivo = Lote::create([
            'identificador'     => 'Z',
            'nombre'            => 'Lote Deshabilitado',
            'ubicacion'         => 'Sector Inactivo',
            'area_ha'           => 1.00,
            'id_tipo_preferido' => 1,
            'estado'            => 'en_descanso',
            'activo'            => false,
            'es_alternativo'    => false,
        ]);

        $responseLoteInactivo = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.store'), [
                'id_usuario' => $this->worker1->id,
                'id_lote'    => $loteInactivo->id,
            ]);

        $responseLoteInactivo->assertSessionHasErrors(['id_lote']);
    }

    /**
     * Criterio 4: Consulta de lotes asignados por trabajador
     * Dado que necesito revisar la distribución actual de responsabilidades,
     * cuando consulto el detalle de un trabajador, entonces el sistema debe mostrar sus lotes asignados y su estado.
     */
    public function test_criterio_4_consulta_de_lotes_asignados_por_trabajador(): void
    {
        // Asignar lote A y lote B al trabajador 1
        AsignacionLote::create([
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteA->id}_user_{$this->worker1->id}",
            'creado_en'    => now(),
        ]);

        AsignacionLote::create([
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteB->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteB->id}_user_{$this->worker1->id}",
            'creado_en'    => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('usuarios.show', $this->worker1));

        $response->assertOk();
        $response->assertSee('Lote A - Lote Los Pinos');
        $response->assertSee('Lote B - Lote El Manantial');
        $response->assertSee('disponible');
        $response->assertSee('ocupado');
    }

    /**
     * Criterio 5: Desactivar asignación sin perder historial
     * Dado que un trabajador deja de ser responsable de un lote,
     * cuando desactivo la asignación vigente, entonces el sistema debe conservar el historial.
     */
    public function test_criterio_5_desactivar_asignacion_sin_perder_historial(): void
    {
        $asig = AsignacionLote::create([
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteA->id}_user_{$this->worker1->id}",
            'creado_en'    => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.desactivar'), [
                'id_usuario' => $this->worker1->id,
                'id_lote'    => $this->loteA->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // El registro sigue existiendo físicamente en BD (historial conservado)
        $this->assertDatabaseHas('asignaciones_lote', [
            'id'           => $asig->id,
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'activo'       => false,
            'clave_activa' => null,
        ]);

        // Ya no figura en los lotes activos del trabajador
        $this->assertFalse($this->worker1->fresh()->lotes->contains('id', $this->loteA->id));

        // Pero sí figura en el historial de asignaciones
        $this->assertTrue($this->worker1->fresh()->asignacionesLote->contains('id', $asig->id));
    }

    /**
     * Criterio 6: Reasignación controlada de lote
     * Dado que necesito mover la responsabilidad de un lote a otro trabajador,
     * cuando cierro una asignación y registro una nueva, entonces el sistema mantiene la trazabilidad,
     * conservando el historial y dejando activa solo la nueva asignación.
     */
    public function test_criterio_6_reasignacion_controlada_de_lote(): void
    {
        // Asignación inicial a Worker 1
        $asigInicial = AsignacionLote::create([
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteA->id}_user_{$this->worker1->id}",
            'creado_en'    => now()->subDays(10),
        ]);

        // Reasignar de Worker 1 a Worker 2
        $response = $this->actingAs($this->admin)
            ->post(route('asignaciones.lotes.reasignar'), [
                'id_lote'             => $this->loteA->id,
                'id_usuario_anterior' => $this->worker1->id,
                'id_usuario_nuevo'    => $this->worker2->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // 1. Asignación previa cerrada con historial
        $this->assertDatabaseHas('asignaciones_lote', [
            'id'           => $asigInicial->id,
            'id_usuario'   => $this->worker1->id,
            'id_lote'      => $this->loteA->id,
            'activo'       => false,
            'clave_activa' => null,
        ]);

        // 2. Nueva asignación activa para Worker 2
        $this->assertDatabaseHas('asignaciones_lote', [
            'id_usuario'   => $this->worker2->id,
            'id_lote'      => $this->loteA->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteA->id}_user_{$this->worker2->id}",
        ]);

        // 3. Worker 1 ya no tiene activo el lote; Worker 2 sí lo tiene activo
        $this->assertFalse($this->worker1->fresh()->lotes->contains('id', $this->loteA->id));
        $this->assertTrue($this->worker2->fresh()->lotes->contains('id', $this->loteA->id));

        // 4. Historial total contiene ambos registros
        $this->assertEquals(2, AsignacionLote::where('id_lote', $this->loteA->id)->count());
    }
}
