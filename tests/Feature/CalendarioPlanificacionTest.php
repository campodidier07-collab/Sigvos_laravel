<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Usuario;
use App\Models\Variedad;
use Carbon\Carbon;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioPlanificacionTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $trabajador;
    protected Lote $lote;
    protected Cultivo $cultivo;
    protected TipoActividad $tipoRiego;
    protected TipoActividad $tipoFumigacion;
    protected TipoActividad $tipoCosecha;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'nombre' => 'Admin Calendario',
            'email'  => 'admin_cal@sigvos.test',
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->trabajador = Usuario::factory()->create([
            'nombre' => 'Pedro Trabajador',
            'email'  => 'pedro@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->lote = Lote::create([
            'identificador'     => 'CAL1',
            'nombre'            => 'Lote Calendario 1',
            'ubicacion'         => 'Sector Oriente',
            'area_ha'           => 3.5,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $variedad = Variedad::first();

        $this->cultivo = Cultivo::create([
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-CAL-001',
            'fecha_siembra'          => Carbon::now()->subMonths(1)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addDays(20)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->tipoRiego = TipoActividad::where('codigo', 'RIEGO')->first();
        $this->tipoFumigacion = TipoActividad::where('codigo', 'FUMIGACION')->first();
        $this->tipoCosecha = TipoActividad::where('codigo', 'COSECHA')->first();
    }

    /**
     * Criterio 1: Vista mensual del calendario.
     * Dado que accedo al calendario mensual, cuando selecciono el mes actual,
     * entonces el sistema debe mostrar las actividades programadas y fechas de cosecha del mes.
     */
    public function test_criterio_1_vista_mensual_del_calendario(): void
    {
        $fechaMes = Carbon::now()->startOfMonth()->addDays(10)->toDateString();

        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Riego programado del mes',
            'fecha_programada'  => $fechaMes,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $start = Carbon::now()->startOfMonth()->toDateString();
        $end = Carbon::now()->endOfMonth()->toDateString();

        // 1. Acceso a la vista principal
        $viewResponse = $this->actingAs($this->admin)->get(route('calendario.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertViewIs('calendario.index');
        $viewResponse->assertViewHas('tiposActividad');

        // 2. Consulta de eventos del mes
        $eventosResponse = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start' => $start,
            'end'   => $end,
        ]));

        $eventosResponse->assertStatus(200);
        $json = $eventosResponse->json();

        // Debe incluir la actividad programada
        $ids = collect($json)->pluck('id')->toArray();
        $this->assertContains('act_' . $actividad->id, $ids);

        // Y la fecha de cosecha si cae en el rango del mes
        if ($this->cultivo->fecha_cosecha_estimada >= $start && $this->cultivo->fecha_cosecha_estimada <= $end) {
            $this->assertContains('cosecha_' . $this->cultivo->id, $ids);
        }
    }

    /**
     * Criterio 2: Vista semanal detallada.
     * Dado que necesito planificar la semana, cuando selecciono la vista semanal,
     * entonces el sistema debe mostrar el detalle de las actividades programadas organizadas por día.
     */
    public function test_criterio_2_vista_semanal_detallada(): void
    {
        $inicioSemana = Carbon::now()->startOfWeek()->toDateString();
        $finSemana = Carbon::now()->endOfWeek()->toDateString();

        $fechaActividadSemana = Carbon::now()->startOfWeek()->addDays(2)->toDateString();

        $actividadSemanal = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación intensiva semanal',
            'fecha_programada'  => $fechaActividadSemana,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start' => $inicioSemana,
            'end'   => $finSemana,
        ]));

        $response->assertStatus(200);
        $json = $response->json();

        $eventoEncontrado = collect($json)->firstWhere('id', 'act_' . $actividadSemanal->id);
        $this->assertNotNull($eventoEncontrado);
        $this->assertEquals($fechaActividadSemana, $eventoEncontrado['start']);
        $this->assertArrayHasKey('extendedProps', $eventoEncontrado);
        $this->assertEquals($actividadSemanal->descripcion, $eventoEncontrado['extendedProps']['descripcion']);
    }

    /**
     * Criterio 3: Navegación desde el calendario al detalle de actividad.
     * Dado que visualizo una actividad en el calendario, cuando la selecciono,
     * entonces el sistema debe dirigirme a su detalle.
     */
    public function test_criterio_3_navegacion_al_detalle_de_actividad(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Actividad para detalle',
            'fecha_programada'  => Carbon::tomorrow()->toDateString(),
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start' => Carbon::today()->toDateString(),
            'end'   => Carbon::now()->addDays(5)->toDateString(),
        ]));

        $evento = collect($response->json())->firstWhere('id', 'act_' . $actividad->id);
        $this->assertNotNull($evento);
        $this->assertEquals(route('actividades.show', $actividad->id), $evento['url']);

        // Verificar que la ruta de detalle responda 200 y muestre los datos
        $detalleResponse = $this->actingAs($this->admin)->get($evento['url']);
        $detalleResponse->assertStatus(200);
        $detalleResponse->assertSee('Actividad para detalle');
    }

    /**
     * Criterio 4: Indicadores visuales de actividades del día actual.
     * Dado que estoy visualizando el calendario, cuando existen actividades programadas para el día actual,
     * entonces el sistema debe resaltarlas.
     */
    public function test_criterio_4_indicadores_visuales_de_actividades_del_dia(): void
    {
        $hoy = Carbon::today()->toDateString();

        $actividadHoy = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Fumigación de HOY prioritaria',
            'fecha_programada'  => $hoy,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start' => Carbon::now()->subDays(2)->toDateString(),
            'end'   => Carbon::now()->addDays(2)->toDateString(),
        ]));

        $evento = collect($response->json())->firstWhere('id', 'act_' . $actividadHoy->id);
        $this->assertNotNull($evento);

        // Debe tener el prefijo HOY y clase de resaltado
        $this->assertStringContainsString('HOY', $evento['title']);
        $this->assertStringContainsString('fc-event-today-highlight', $evento['className']);
        $this->assertTrue($evento['extendedProps']['es_hoy']);
    }

    /**
     * Criterio 5: Visualización de próximas cosechas en calendario.
     * Dado que tengo cultivos activos, cuando consulto el calendario,
     * entonces el sistema debe mostrar las fechas estimadas de cosecha.
     */
    public function test_criterio_5_visualizacion_de_proximas_cosechas_en_calendario(): void
    {
        $fechaCosecha = Carbon::now()->addDays(15)->toDateString();
        $this->cultivo->update(['fecha_cosecha_estimada' => $fechaCosecha]);

        $response = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start' => Carbon::today()->toDateString(),
            'end'   => Carbon::now()->addDays(30)->toDateString(),
        ]));

        $eventoCosecha = collect($response->json())->firstWhere('id', 'cosecha_' . $this->cultivo->id);
        $this->assertNotNull($eventoCosecha);
        $this->assertEquals($fechaCosecha, $eventoCosecha['start']);
        $this->assertEquals('cosecha', $eventoCosecha['extendedProps']['tipo_evento']);
        $this->assertStringContainsString('Cosecha Estimada', $eventoCosecha['title']);
        $this->assertEquals(route('cultivos.show', $this->cultivo->id), $eventoCosecha['url']);
    }

    /**
     * Criterio 6: Filtrado de calendario por tipo de actividad.
     * Dado que necesito ver actividades de un tipo específico, cuando aplico un filtro,
     * entonces el sistema debe mostrar únicamente las actividades del tipo seleccionado.
     */
    public function test_criterio_6_filtrado_de_calendario_por_tipo_de_actividad(): void
    {
        $fecha = Carbon::now()->addDays(3)->toDateString();

        $actRiego = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Labor de riego',
            'fecha_programada'  => $fecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        $actFumigacion = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'descripcion'       => 'Labor de fumigación',
            'fecha_programada'  => $fecha,
            'estado'            => 'pendiente',
            'asignado_a'        => $this->trabajador->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Filtrar solo por RIEGO
        $response = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start'             => Carbon::today()->toDateString(),
            'end'               => Carbon::now()->addDays(10)->toDateString(),
            'id_tipo_actividad' => $this->tipoRiego->id,
        ]));

        $response->assertStatus(200);
        $ids = collect($response->json())->pluck('id')->toArray();

        $this->assertContains('act_' . $actRiego->id, $ids);
        $this->assertNotContains('act_' . $actFumigacion->id, $ids);
    }

    /**
     * Criterio 7: Agregar actividad directamente desde el calendario.
     * Dado que estoy visualizando el calendario, cuando selecciono un día y agrego una actividad,
     * entonces el sistema debe permitir su registro abriendo el formulario con la fecha precargada.
     */
    public function test_criterio_7_agregar_actividad_directamente_desde_el_calendario(): void
    {
        $fechaElegida = Carbon::now()->addDays(7)->toDateString();

        // 1. Acceder al formulario pasando la fecha preseleccionada
        $formResponse = $this->actingAs($this->admin)->get(route('actividades.create', ['fecha' => $fechaElegida]));
        $formResponse->assertStatus(200);
        $formResponse->assertSee($fechaElegida);

        // 2. Registrar la actividad para esa fecha
        $postResponse = $this->actingAs($this->admin)->post(route('actividades.store'), [
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'descripcion'       => 'Actividad creada directamente desde fecha del calendario',
            'fecha_programada'  => $fechaElegida,
            'asignado_a'        => $this->trabajador->id,
        ]);

        $postResponse->assertRedirect(route('actividades.index'));

        $this->assertDatabaseHas('actividades', [
            'descripcion' => 'Actividad creada directamente desde fecha del calendario',
            'id_cultivo'  => $this->cultivo->id,
        ]);
        $actividadInsertada = Actividad::where('descripcion', 'Actividad creada directamente desde fecha del calendario')->first();
        $this->assertNotNull($actividadInsertada);
        $this->assertEquals($fechaElegida, Carbon::parse($actividadInsertada->fecha_programada)->toDateString());

        // 3. Confirmar que ahora aparece reflejada en el calendario
        $calResponse = $this->actingAs($this->admin)->getJson(route('calendario.eventos', [
            'start' => Carbon::now()->addDays(5)->toDateString(),
            'end'   => Carbon::now()->addDays(10)->toDateString(),
        ]));

        $actividadCreada = Actividad::where('descripcion', 'Actividad creada directamente desde fecha del calendario')->first();
        $this->assertNotNull($actividadCreada);

        $ids = collect($calResponse->json())->pluck('id')->toArray();
        $this->assertContains('act_' . $actividadCreada->id, $ids);
    }
}
