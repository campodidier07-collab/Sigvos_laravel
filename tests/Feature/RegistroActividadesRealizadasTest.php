<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lote;
use App\Models\Cultivo;
use App\Models\Variedad;
use App\Models\TipoActividad;
use App\Models\Actividad;
use Carbon\Carbon;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistroActividadesRealizadasTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Lote $lote;
    protected Cultivo $cultivo1;
    protected Cultivo $cultivo2;
    protected TipoActividad $tipoFertilizacion;
    protected TipoActividad $tipoFumigacion;
    protected TipoActividad $tipoPoda;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->lote = Lote::create([
            'identificador'     => 'DP13-L1',
            'nombre'            => 'LOTE-NORTE',
            'ubicacion'         => 'Sector Norte Finca',
            'area_ha'           => 5.0,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $variedadCafe = Variedad::where('nombre', 'Castillo')->first() ?? Variedad::first();
        $variedadMaiz = Variedad::where('nombre', 'Maíz Amarillo')->first() ?? Variedad::first();

        $this->cultivo1 = Cultivo::create([
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $variedadCafe->id,
            'codigo'                 => 'CUL-DP13-001',
            'fecha_siembra'          => Carbon::now()->subMonths(6)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addMonths(3)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->cultivo2 = Cultivo::create([
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $variedadMaiz->id,
            'codigo'                 => 'CUL-DP13-002',
            'fecha_siembra'          => Carbon::now()->subMonths(2)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addMonths(2)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => null,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->tipoFertilizacion = TipoActividad::where('codigo', 'FERTILIZACION')->first();
        $this->tipoFumigacion    = TipoActividad::where('codigo', 'FUMIGACION')->first();
        $this->tipoPoda          = TipoActividad::where('codigo', 'PODA')->first();
    }

    /**
     * Criterio 1: Registro de actividad realizada exitosamente.
     * Cuando registro la actividad indicando tipo, fecha, descripción y cultivo asociado,
     * el sistema la guarda correctamente con estado completada y muestra confirmación.
     */
    public function test_criterio_1_registro_de_actividad_realizada_exitosamente(): void
    {
        $fechaPasada = Carbon::now()->subDays(2)->toDateString();

        $response = $this->actingAs($this->admin)->post(route('actividades.registrar-realizada'), [
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'fecha_realizacion' => $fechaPasada,
            'descripcion' => 'Aplicación de 50g de triple 15 por planta en todo el lote.',
            'observaciones' => 'Suelo húmedo tras lluvia de la mañana.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actividades', [
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'estado' => 'completada',
        ]);

        $actividad = Actividad::where('id_cultivo', $this->cultivo1->id)->first();
        $this->assertNotNull($actividad);
        $this->assertEquals($fechaPasada, Carbon::parse($actividad->fecha_ejecucion)->toDateString());
        $this->assertEquals('Aplicación de 50g de triple 15 por planta en todo el lote.', $actividad->descripcion);
    }

    /**
     * Criterio 2: Registro de actividades agrícolas por tipo.
     * Permite seleccionar el tipo (fertilización, fumigación o poda), completar la info requerida,
     * guardarla y vincularla al historial del cultivo correspondiente.
     */
    public function test_criterio_2_registro_de_actividades_agricolas_por_tipo(): void
    {
        $tipos = [
            $this->tipoFertilizacion->id => 'Fertilización completa con urea al 46%.',
            $this->tipoFumigacion->id    => 'Fumigación preventiva contra roya usando caldo bordelés.',
            $this->tipoPoda->id          => 'Poda sanitaria eliminando ramas afectadas por broca.',
        ];

        foreach ($tipos as $idTipo => $desc) {
            $resp = $this->actingAs($this->admin)->post(route('actividades.registrar-realizada'), [
                'id_cultivo' => $this->cultivo1->id,
                'id_tipo_actividad' => $idTipo,
                'fecha_realizacion' => Carbon::now()->subDay()->toDateString(),
                'descripcion' => $desc,
            ]);
            $resp->assertSessionHasNoErrors();
        }

        // Verificar vinculación al cultivo
        $actividadesDelCultivo = $this->cultivo1->fresh()->actividades;
        $this->assertCount(3, $actividadesDelCultivo);

        $tiposRegistrados = $actividadesDelCultivo->pluck('id_tipo_actividad')->toArray();
        $this->assertContains($this->tipoFertilizacion->id, $tiposRegistrados);
        $this->assertContains($this->tipoFumigacion->id, $tiposRegistrados);
        $this->assertContains($this->tipoPoda->id, $tiposRegistrados);
    }

    /**
     * Criterio 3: Validación de fecha no futura.
     * Cuando intento guardar una actividad con fecha futura, muestra error y no permite continuar.
     */
    public function test_criterio_3_validacion_de_fecha_no_futura(): void
    {
        $fechaFutura = Carbon::now()->addDays(2)->toDateString();

        $response = $this->actingAs($this->admin)->post(route('actividades.registrar-realizada'), [
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoPoda->id,
            'fecha_realizacion' => $fechaFutura,
            'descripcion' => 'Intento de registro de poda realizada en el futuro.',
        ]);

        $response->assertSessionHasErrors(['fecha_realizacion']);
        $this->assertDatabaseMissing('actividades', [
            'descripcion' => 'Intento de registro de poda realizada en el futuro.',
        ]);
    }

    /**
     * Criterio 4: Consulta de historial de actividades por cultivo.
     * Cuando filtro por un cultivo específico, muestra el historial de actividades de ese cultivo.
     */
    public function test_criterio_4_consulta_de_historial_de_actividades_por_cultivo(): void
    {
        // Actividad para Cultivo 1
        Actividad::create([
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'creado_por' => $this->admin->id,
            'descripcion' => 'Abono orgánico en cultivo de café.',
            'fecha_programada' => Carbon::now()->subDays(5)->toDateString(),
            'fecha_ejecucion' => Carbon::now()->subDays(5)->toDateString(),
            'estado' => 'completada',
        ]);

        // Actividad para Cultivo 2
        Actividad::create([
            'id_cultivo' => $this->cultivo2->id,
            'id_tipo_actividad' => $this->tipoFumigacion->id,
            'creado_por' => $this->admin->id,
            'descripcion' => 'Control de gusano cogollero en maíz.',
            'fecha_programada' => Carbon::now()->subDays(3)->toDateString(),
            'fecha_ejecucion' => Carbon::now()->subDays(3)->toDateString(),
            'estado' => 'completada',
        ]);

        // Filtrar por Cultivo 1
        $response = $this->actingAs($this->admin)->get(route('actividades.index', [
            'id_cultivo' => $this->cultivo1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Abono orgánico en cultivo de café.');
        $response->assertDontSee('Control de gusano cogollero en maíz.');
    }

    /**
     * Criterio 5: Consulta de historial por tipo de actividad.
     * Cuando filtro por tipo de actividad, muestra las actividades del tipo seleccionado.
     */
    public function test_criterio_5_consulta_de_historial_por_tipo_de_actividad(): void
    {
        // Crear fertilización
        Actividad::create([
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'creado_por' => $this->admin->id,
            'descripcion' => 'Fertilización radicular con compostaje.',
            'fecha_programada' => Carbon::now()->subDays(4)->toDateString(),
            'fecha_ejecucion' => Carbon::now()->subDays(4)->toDateString(),
            'estado' => 'completada',
        ]);

        // Crear poda
        Actividad::create([
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoPoda->id,
            'creado_por' => $this->admin->id,
            'descripcion' => 'Deschuponado general en las hileras 1 a 10.',
            'fecha_programada' => Carbon::now()->subDays(2)->toDateString(),
            'fecha_ejecucion' => Carbon::now()->subDays(2)->toDateString(),
            'estado' => 'completada',
        ]);

        // Filtrar por Fertilización
        $response = $this->actingAs($this->admin)->get(route('actividades.index', [
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Fertilización radicular con compostaje.');
        $response->assertDontSee('Deschuponado general en las hileras 1 a 10.');
    }

    /**
     * Criterio 6: Validación de descripción mínima.
     * Cuando intento guardar con una descripción menor a la permitida (min: 10 chars),
     * muestra error y no permite guardar.
     */
    public function test_criterio_6_validacion_de_descripcion_minima(): void
    {
        // Intento con descripción demasiado corta (4 caracteres)
        $response = $this->actingAs($this->admin)->post(route('actividades.registrar-realizada'), [
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoPoda->id,
            'fecha_realizacion' => Carbon::now()->toDateString(),
            'descripcion' => 'Poda',
        ]);

        $response->assertSessionHasErrors(['descripcion']);
        $this->assertDatabaseMissing('actividades', [
            'descripcion' => 'Poda',
        ]);

        // Intento en programación regular con descripción corta
        $responseProgramar = $this->actingAs($this->admin)->post(route('actividades.store'), [
            'id_cultivo' => $this->cultivo1->id,
            'id_tipo_actividad' => $this->tipoPoda->id,
            'id_usuario' => $this->admin->id,
            'fecha_programada' => Carbon::now()->addDays(5)->toDateString(),
            'descripcion' => 'Corta',
        ]);

        $responseProgramar->assertSessionHasErrors(['descripcion']);
    }
}
