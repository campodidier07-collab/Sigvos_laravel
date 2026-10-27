<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Notificacion;
use App\Models\TipoActividad;
use App\Models\Usuario;
use App\Models\Variedad;
use App\Services\RecordatorioService;
use Carbon\Carbon;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModificacionReagendamientoActividadesTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $trabajador;
    protected Lote $loteA;
    protected Lote $loteB;
    protected Cultivo $cultivo1;
    protected Cultivo $cultivo2;
    protected TipoActividad $tipoRiego;
    protected TipoActividad $tipoFumigacion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'nombre' => 'Admin Planificador',
            'email'  => 'admin_plan@sigvos.test',
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->trabajador = Usuario::factory()->create([
            'nombre' => 'Juan Operador',
            'email'  => 'juan_op@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->loteA = Lote::create([
            'identificador'     => 'DP12A',
            'nombre'            => 'Lote Norte 12',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 4.0,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $this->loteB = Lote::create([
            'identificador'     => 'DP12B',
            'nombre'            => 'Lote Sur 12',
            'ubicacion'         => 'Sector Sur',
            'area_ha'           => 6.0,
            'id_tipo_preferido' => 2,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $variedad = Variedad::first();

        $this->cultivo1 = Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-DP12-001',
            'fecha_siembra'          => Carbon::now()->subMonths(2)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addDays(40)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->loteA->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->cultivo2 = Cultivo::create([
            'id_lote'                => $this->loteB->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-DP12-002',
            'fecha_siembra'          => Carbon::now()->subMonths(1)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addDays(60)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->loteB->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->tipoRiego = TipoActividad::where('codigo', 'RIEGO')->first();
        $this->tipoFumigacion = TipoActividad::where('codigo', 'FUMIGACION')->first();
    }

    /**
     * Criterio 1: Reagendar actividad programada.
     * Dado que tengo una actividad programada pendiente, cuando cambio su fecha,
     * entonces el sistema debe actualizar la programación, guardar la nueva fecha y actualizar el recordatorio.
     */
    public function test_criterio_1_reagendar_actividad_programada(): void
    {
        $fechaInicial = Carbon::tomorrow()->toDateString();
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Riego programado a reagendar',
            'fecha_programada'  => $fechaInicial,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Generar recordatorio inicial
        app(RecordatorioService::class)->evaluarActividadesProximas();
        $this->assertDatabaseHas('notificaciones', ['id_actividad' => $actividad->id]);

        $nuevaFecha = Carbon::now()->addDays(8)->toDateString();
        $response = $this->actingAs($this->admin)->put(route('actividades.update', $actividad), [
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Riego programado a reagendar',
            'fecha_programada'  => $nuevaFecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $response->assertRedirect(route('actividades.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actividades', [
            'id'     => $actividad->id,
            'estado' => 'pendiente',
        ]);
        $this->assertEquals($nuevaFecha, Carbon::parse($actividad->fresh()->fecha_programada)->toDateString());

        // El recordatorio de mañana debe haberse eliminado
        $this->assertDatabaseMissing('notificaciones', ['id_actividad' => $actividad->id]);
    }

    /**
     * Criterio 2: Modificar descripción de actividad pendiente.
     * Dado que necesito ajustar la información de una actividad pendiente, cuando modifico su descripción,
     * entonces el sistema debe guardar el cambio sin alterar los demás datos.
     */
    public function test_criterio_2_modificar_descripcion_de_actividad_pendiente(): void
    {
        $fecha = Carbon::now()->addDays(4)->toDateString();
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Descripción original',
            'fecha_programada'  => $fecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $nuevaDescripcion = 'Descripción actualizada con dosificación exacta de 300ml';
        $response = $this->actingAs($this->admin)->put(route('actividades.update', $actividad), [
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => $nuevaDescripcion,
            'fecha_programada'  => $fecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $response->assertRedirect(route('actividades.index'));

        $this->assertDatabaseHas('actividades', [
            'id'                => $actividad->id,
            'descripcion'       => $nuevaDescripcion,
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'asignado_a'        => $this->trabajador->id,
            'estado'            => 'pendiente',
        ]);
        $this->assertEquals($fecha, Carbon::parse($actividad->fresh()->fecha_programada)->toDateString());
    }

    /**
     * Criterio 3: Cambiar cultivo objetivo de actividad.
     * Dado que necesito corregir el cultivo asociado a una actividad, cuando realizo el cambio,
     * entonces el sistema debe actualizar la relación correspondiente y guardar el cambio.
     */
    public function test_criterio_3_cambiar_cultivo_objetivo_de_actividad(): void
    {
        $fecha = Carbon::now()->addDays(5)->toDateString();
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id, // Cultivo 1 inicial
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Labor asignada a cultivo erróneo',
            'fecha_programada'  => $fecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Cambiar a Cultivo 2
        $response = $this->actingAs($this->admin)->put(route('actividades.update', $actividad), [
            'id_cultivo'        => $this->cultivo2->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Labor asignada a cultivo corregido',
            'fecha_programada'  => $fecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $response->assertRedirect(route('actividades.index'));

        $this->assertDatabaseHas('actividades', [
            'id'         => $actividad->id,
            'id_cultivo' => $this->cultivo2->id,
        ]);
        $this->assertEquals($this->cultivo2->id, $actividad->fresh()->id_cultivo);
    }

    /**
     * Criterio 4: Restricción de modificación de actividades completadas.
     * Dado que una actividad ya fue completada, cuando intento modificarla,
     * entonces el sistema debe bloquear la edición y mostrar una advertencia.
     */
    public function test_criterio_4_restriccion_de_modificacion_de_actividades_completadas(): void
    {
        $actividadCompletada = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Actividad previamente ejecutada',
            'fecha_programada'  => Carbon::yesterday()->toDateString(),
            'fecha_ejecucion'   => Carbon::yesterday()->toDateString(),
            'estado'            => 'completada',
            'asignado_a'        => $this->trabajador->id,
            'ejecutado_por'     => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // 1. Intentar acceder a la vista de edición: debe ser redirigido con error
        $editResponse = $this->actingAs($this->admin)->get(route('actividades.edit', $actividadCompletada));
        $editResponse->assertRedirect(route('actividades.show', $actividadCompletada));
        $editResponse->assertSessionHas('error');

        // 2. Intentar actualizar mediante PUT: debe ser bloqueado sin alterar el registro
        $putResponse = $this->actingAs($this->admin)->put(route('actividades.update', $actividadCompletada), [
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Intento malicioso de alterar una labor completada',
            'fecha_programada'  => Carbon::now()->addDays(3)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $putResponse->assertRedirect(route('actividades.show', $actividadCompletada));
        $putResponse->assertSessionHas('error');

        $this->assertEquals('Actividad previamente ejecutada', $actividadCompletada->fresh()->descripcion);
        $this->assertEquals('completada', $actividadCompletada->fresh()->estado);
    }

    /**
     * Criterio 5: Cancelar actividad programada.
     * Dado que decido no realizar una actividad programada, cuando selecciono la opción de cancelar actividad,
     * entonces el sistema cancela la actividad y la retira de la programación activa.
     */
    public function test_criterio_5_cancelar_actividad_programada(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación que no se llevará a cabo',
            'fecha_programada'  => Carbon::now()->addDays(2)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Ejecutar acción de cancelación
        $response = $this->actingAs($this->admin)->post(route('actividades.cancelar', $actividad));

        $response->assertSessionHas('success');
        $this->assertEquals('cancelada', $actividad->fresh()->estado);

        // En el listado no figura como pendiente
        $this->assertDatabaseMissing('actividades', [
            'id'     => $actividad->id,
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Criterio 6: Validación de fecha futura al reagendar.
     * Dado que estoy reprogramando una actividad, cuando intento guardar una fecha anterior a la actual,
     * entonces el sistema debe validar esa restricción, mostrar un error y no permitir guardar.
     */
    public function test_criterio_6_validacion_de_fecha_futura_al_reagendar(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Actividad a reprogramar',
            'fecha_programada'  => Carbon::tomorrow()->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $fechaAyer = Carbon::yesterday()->toDateString();
        $response = $this->actingAs($this->admin)->put(route('actividades.update', $actividad), [
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Intento de reagendar a fecha pasada',
            'fecha_programada'  => $fechaAyer,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $response->assertSessionHasErrors(['fecha_programada']);

        // La fecha original no debió alterarse
        $this->assertNotEquals($fechaAyer, Carbon::parse($actividad->fresh()->fecha_programada)->toDateString());
    }

    /**
     * Criterio 7: Modificación masiva de actividades recurrentes.
     * Dado que una actividad pertenece a una programación recurrente, cuando realizo una modificación,
     * entonces el sistema debe permitir elegir el alcance del cambio (una o varias ocurrencias).
     */
    public function test_criterio_7_modificacion_masiva_de_actividades_recurrentes(): void
    {
        $grupoRecurrencia = 'SERIE-RIEGO-2026';

        $act1 = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Riego recurrente semana 1',
            'fecha_programada'  => Carbon::now()->addDays(2)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
            'grupo_recurrencia' => $grupoRecurrencia,
        ]);

        $act2 = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Riego recurrente semana 2',
            'fecha_programada'  => Carbon::now()->addDays(9)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
            'grupo_recurrencia' => $grupoRecurrencia,
        ]);

        $act3 = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Riego recurrente semana 3',
            'fecha_programada'  => Carbon::now()->addDays(16)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
            'grupo_recurrencia' => $grupoRecurrencia,
        ]);

        // Caso A: Modificar solo esta actividad
        $this->actingAs($this->admin)->put(route('actividades.update', $act1), [
            'id_cultivo'          => $this->cultivo1->id,
            'id_tipo_actividad'   => $this->tipoRiego->id,
            'descripcion'         => 'Riego individual ajustado',
            'fecha_programada'    => Carbon::now()->addDays(3)->toDateString(),
            'estado'              => 'pendiente',
            'asignado_a'          => $this->trabajador->id,
            'alcance_recurrencia' => 'solo_esta',
        ]);

        $this->assertEquals('Riego individual ajustado', $act1->fresh()->descripcion);
        $this->assertEquals('Riego recurrente semana 2', $act2->fresh()->descripcion);

        // Caso B: Modificar masivamente todas las pendientes de la serie recurrente
        $this->actingAs($this->admin)->put(route('actividades.update', $act2), [
            'id_cultivo'          => $this->cultivo2->id, // Reasigna cultivo masivamente
            'id_tipo_actividad'   => $this->tipoFumigacion->id, // Cambia labor a fumigación
            'descripcion'         => 'Labor recurrente actualizada para todo el bloque',
            'fecha_programada'    => Carbon::now()->addDays(9)->toDateString(),
            'estado'              => 'pendiente',
            'asignado_a'          => $this->trabajador->id,
            'alcance_recurrencia' => 'todas_pendientes',
        ]);

        // Tanto act2 como act3 deben haber adoptado la nueva descripción y cultivo
        $this->assertEquals('Labor recurrente actualizada para todo el bloque', $act2->fresh()->descripcion);
        $this->assertEquals('Labor recurrente actualizada para todo el bloque', $act3->fresh()->descripcion);
        $this->assertEquals($this->cultivo2->id, $act3->fresh()->id_cultivo);
    }

    /**
     * Criterio 8: Notificación de cambios en actividades.
     * Dado que realizo cambios en actividades programadas, cuando guardo la modificación,
     * entonces el sistema debe mostrar una notificación confirmando la actualización.
     */
    public function test_criterio_8_notificacion_de_cambios_en_actividades(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Actividad previa a notificación',
            'fecha_programada'  => Carbon::now()->addDays(4)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('actividades.update', $actividad), [
            'id_cultivo'        => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Actividad con cambios guardados',
            'fecha_programada'  => Carbon::now()->addDays(5)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $response->assertRedirect(route('actividades.index'));
        $response->assertSessionHas('success', 'Actividad actualizada correctamente.');
    }
}
