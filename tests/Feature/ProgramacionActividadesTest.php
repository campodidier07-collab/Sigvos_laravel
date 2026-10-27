<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Notificacion;
use App\Models\TipoActividad;
use App\Models\Usuario;
use App\Services\RecordatorioService;
use Carbon\Carbon;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramacionActividadesTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $trabajador;
    protected Lote $lote;
    protected Cultivo $cultivo;
    protected TipoActividad $tipoFumigacion;
    protected TipoActividad $tipoFertilizacion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'nombre' => 'Admin Sigvos',
            'email'  => 'admin@sigvos.test',
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->trabajador = Usuario::factory()->create([
            'nombre' => 'Juan Operario',
            'email'  => 'juan@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->lote = Lote::create([
            'identificador'     => 'DP10',
            'nombre'            => 'Lote Parcela 10',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 5.50,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $variedad = \App\Models\Variedad::first();

        $this->cultivo = Cultivo::create([
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-DP10-001',
            'fecha_siembra'          => Carbon::now()->subMonths(2)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addDays(10)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->tipoFumigacion = TipoActividad::where('codigo', 'FUMIGACION')->first();
        $this->tipoFertilizacion = TipoActividad::where('codigo', 'FERTILIZACION')->first();
    }

    /**
     * Criterio 1: Programación de actividad futura exitosa.
     * Cuando agendo la actividad con fecha futura, tipo, cultivo y descripción ->
     * el sistema crea la actividad programada en estado 'pendiente' y muestra confirmación.
     */
    public function test_criterio_1_programacion_de_actividad_futura_exitosa(): void
    {
        $fechaFutura = Carbon::now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->admin)->post(route('actividades.store'), [
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación de control biológico contra gusano cogollero',
            'fecha_programada'  => $fechaFutura,
            'asignado_a'        => $this->trabajador->id,
            'prioridad'         => 'alta',
        ]);

        $response->assertRedirect(route('actividades.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actividades', [
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);
        $actividadCreada = Actividad::latest('id')->first();
        $this->assertEquals($fechaFutura, Carbon::parse($actividadCreada->fecha_programada)->toDateString());
    }

    /**
     * Criterio 2: Recordatorio automático de actividad próxima.
     * Cuando llega el día anterior a la actividad programada ->
     * el sistema genera y envía una notificación recordatoria.
     */
    public function test_criterio_2_recordatorio_automatico_de_actividad_proxima(): void
    {
        $fechaManana = Carbon::tomorrow()->toDateString();

        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación preventiva de roya',
            'fecha_programada'  => $fechaManana,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $service = app(RecordatorioService::class);
        $totalRecordatorios = $service->evaluarActividadesProximas();

        $this->assertGreaterThanOrEqual(1, $totalRecordatorios);

        // Debe haberse creado notificación para el trabajador asignado
        $this->assertDatabaseHas('notificaciones', [
            'id_usuario'   => $this->trabajador->id,
            'id_actividad' => $actividad->id,
            'tipo'         => 'actividad',
        ]);

        // Y para los administradores
        $this->assertDatabaseHas('notificaciones', [
            'id_usuario'   => $this->admin->id,
            'id_actividad' => $actividad->id,
            'tipo'         => 'actividad',
        ]);
    }

    /**
     * Criterio 3: Alerta de cultivo próximo a cosechar.
     * Cuando el sistema detecta un cultivo cercano a cosecha (< 15 días) ->
     * el sistema genera una alerta de cosecha próxima.
     */
    public function test_criterio_3_alerta_de_cultivo_proximo_a_cosechar(): void
    {
        // El cultivo tiene fecha_estimada_cosecha en 10 días
        $service = app(RecordatorioService::class);
        $alertas = $service->evaluarCosechasProximas();

        $this->assertGreaterThanOrEqual(1, $alertas);

        $this->assertDatabaseHas('notificaciones', [
            'id_usuario' => $this->admin->id,
            'id_cultivo' => $this->cultivo->id,
            'tipo'       => 'cosecha',
        ]);
    }

    /**
     * Criterio 4: Alerta automática de fertilización vencida.
     * Cuando un cultivo activo no registra fertilización en el tiempo definido (> 30 días) ->
     * el sistema muestra una alerta para programar la fertilización.
     */
    public function test_criterio_4_alerta_automatica_de_fertilizacion_vencida(): void
    {
        // El cultivo tiene 60 días desde la siembra y ninguna actividad de fertilización registrada
        $service = app(RecordatorioService::class);
        $alertas = $service->evaluarFertilizacionesVencidas();

        $this->assertGreaterThanOrEqual(1, $alertas);

        $this->assertDatabaseHas('notificaciones', [
            'id_usuario' => $this->admin->id,
            'id_cultivo' => $this->cultivo->id,
            'tipo'       => 'fertilizacion',
        ]);
    }

    /**
     * Criterio 5: Marcar actividad programada como completada.
     * Cuando cambio el estado de la actividad a completada ->
     * el sistema registra la actividad como realizada.
     */
    public function test_criterio_5_marcar_actividad_programada_como_completada(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación de prueba para completar',
            'fecha_programada'  => Carbon::now()->addDays(2)->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Operario completa la actividad mediante endpoint rápido o update
        $response = $this->actingAs($this->trabajador)->post(route('actividades.completar', $actividad));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actividades', [
            'id'            => $actividad->id,
            'estado'        => 'completada',
            'ejecutado_por' => $this->trabajador->id,
        ]);

        $actividadFresca = $actividad->fresh();
        $this->assertNotNull($actividadFresca->fecha_ejecucion);
    }

    /**
     * Criterio 6: Modificar fecha de actividad programada.
     * Cuando actualizo la fecha de una actividad programada antes de ejecutarla ->
     * el sistema guarda la nueva fecha y actualiza el recordatorio.
     */
    public function test_criterio_6_modificar_fecha_de_actividad_programada(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación a reprogramar',
            'fecha_programada'  => Carbon::tomorrow()->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Generar recordatorio inicial
        app(RecordatorioService::class)->evaluarActividadesProximas();
        $this->assertDatabaseHas('notificaciones', [
            'id_actividad' => $actividad->id,
        ]);

        // Reprogramar para dentro de 10 días
        $nuevaFecha = Carbon::now()->addDays(10)->toDateString();
        $response = $this->actingAs($this->admin)->put(route('actividades.update', $actividad), [
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación a reprogramar (actualizada)',
            'fecha_programada'  => $nuevaFecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
        ]);

        $response->assertRedirect(route('actividades.index'));

        $this->assertDatabaseHas('actividades', [
            'id'     => $actividad->id,
            'estado' => 'pendiente',
        ]);
        $this->assertEquals($nuevaFecha, Carbon::parse($actividad->fresh()->fecha_programada)->toDateString());

        // Los recordatorios obsoletos de mañana deben haber sido eliminados
        $this->assertDatabaseMissing('notificaciones', [
            'id_actividad' => $actividad->id,
        ]);
    }

    /**
     * Criterio 7: Cancelar actividad programada.
     * Cuando cancelo la actividad desde el calendario o listado ->
     * el sistema elimina la actividad programada y cancela el recordatorio.
     */
    public function test_criterio_7_cancelar_actividad_programada(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Actividad a cancelar',
            'fecha_programada'  => Carbon::tomorrow()->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Generar recordatorio
        app(RecordatorioService::class)->evaluarActividadesProximas();
        $this->assertDatabaseHas('notificaciones', ['id_actividad' => $actividad->id]);

        // Opción A: Cancelar por acción cancelar
        $this->actingAs($this->admin)->post(route('actividades.cancelar', $actividad));
        $this->assertDatabaseMissing('notificaciones', ['id_actividad' => $actividad->id]);
        $this->assertEquals('cancelada', $actividad->fresh()->estado);

        // Opción B: Eliminar definitivamente (Criterio 7: "elimina la actividad programada y cancela el recordatorio")
        $response = $this->actingAs($this->admin)->delete(route('actividades.destroy', $actividad));
        $response->assertRedirect(route('actividades.index'));

        $this->assertDatabaseMissing('actividades', ['id' => $actividad->id]);
        $this->assertDatabaseMissing('notificaciones', ['id_actividad' => $actividad->id]);
    }

    /**
     * Criterio 8: Validación de fecha futura para programación.
     * Cuando intento registrar una actividad con fecha pasada ->
     * el sistema muestra un error y no permite continuar.
     */
    public function test_criterio_8_validacion_de_fecha_futura_para_programacion(): void
    {
        $fechaPasada = Carbon::yesterday()->toDateString();

        $response = $this->actingAs($this->admin)->post(route('actividades.store'), [
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Intento inválido con fecha de ayer',
            'fecha_programada'  => $fechaPasada,
            'asignado_a'        => $this->trabajador->id,
        ]);

        // Debe fallar con error de validación en fecha_programada
        $response->assertSessionHasErrors(['fecha_programada']);

        // No debe haberse insertado en base de datos
        $this->assertDatabaseMissing('actividades', [
            'descripcion'      => 'Intento inválido con fecha de ayer',
            'fecha_programada' => $fechaPasada,
        ]);
    }
}
