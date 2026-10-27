<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lote;
use App\Models\Cultivo;
use App\Models\Variedad;
use App\Models\TipoActividad;
use App\Models\Actividad;
use App\Models\FotoCultivo;
use App\Models\AsignacionLote;
use Carbon\Carbon;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CompletarActividadesTrabajadorTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $trabajador1;
    protected Usuario $trabajador2;
    protected Lote $lote1;
    protected Cultivo $cultivo;
    protected TipoActividad $tipoRiego;
    protected TipoActividad $tipoFertilizacion;
    protected Actividad $actividadTrabajador1;
    protected Actividad $actividadTrabajador2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        Storage::fake('public');

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->trabajador1 = Usuario::factory()->create([
            'nombre' => 'Mateo Gomez',
            'email'  => 'mateo@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->trabajador2 = Usuario::factory()->create([
            'nombre' => 'Lucas Perez',
            'email'  => 'lucas@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->lote1 = Lote::create([
            'identificador'     => 'DP15-L1',
            'nombre'            => 'Lote Principal DP15',
            'ubicacion'         => 'Sector Cafetero',
            'area_ha'           => 6.0,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        // Asignar lote a ambos trabajadores
        AsignacionLote::create([
            'id_usuario'   => $this->trabajador1->id,
            'id_lote'      => $this->lote1->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->lote1->id}_user_{$this->trabajador1->id}",
            'creado_en'    => now(),
        ]);

        $variedad = Variedad::first();
        $this->cultivo = Cultivo::create([
            'id_lote'                => $this->lote1->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-DP15-001',
            'fecha_siembra'          => Carbon::now()->subMonths(3)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addMonths(3)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->lote1->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->tipoRiego         = TipoActividad::where('codigo', 'RIEGO')->first();
        $this->tipoFertilizacion = TipoActividad::where('codigo', 'FERTILIZACION')->first();

        // Actividad asignada a trabajador 1
        $this->actividadTrabajador1 = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoRiego->id,
            'asignado_a'        => $this->trabajador1->id,
            'creado_por'        => $this->admin->id,
            'descripcion'       => 'Riego por goteo en sector oeste.',
            'fecha_programada'  => Carbon::now()->toDateString(),
            'estado'            => 'pendiente',
        ]);

        // Actividad asignada a trabajador 2
        $this->actividadTrabajador2 = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'asignado_a'        => $this->trabajador2->id,
            'creado_por'        => $this->admin->id,
            'descripcion'       => 'Fertilización con abono orgánico.',
            'fecha_programada'  => Carbon::now()->toDateString(),
            'estado'            => 'pendiente',
        ]);
    }

    /**
     * Criterio 1: Visualización de actividades pendientes asignadas.
     * Cuando abro el listado de actividades pendientes, el sistema muestra
     * únicamente las actividades asignadas a mí.
     */
    public function test_criterio_1_visualizacion_de_actividades_pendientes_asignadas(): void
    {
        $response = $this->actingAs($this->trabajador1)->get(route('actividades.index', [
            'estado' => 'pendiente',
        ]));

        $response->assertOk();
        // Ve su actividad asignada
        $response->assertSee('Riego por goteo en sector oeste.');
        // NO ve la actividad asignada a trabajador 2
        $response->assertDontSee('Fertilización con abono orgánico.');
    }

    /**
     * Criterio 2: Marcar actividad como realizada.
     * Cuando marco una actividad como "Realizada" y guardo, el sistema actualiza
     * el estado de la actividad y registra la fecha de ejecución.
     */
    public function test_criterio_2_marcar_actividad_como_realizada(): void
    {
        $fechaEjecutada = Carbon::now()->toDateString();

        $response = $this->actingAs($this->trabajador1)->post(route('actividades.completar', $this->actividadTrabajador1), [
            'fecha_ejecucion' => $fechaEjecutada,
            'observaciones'   => 'Labor concluida sin incidentes mecánicos en el sistema de riego.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->actividadTrabajador1->refresh();
        $this->assertEquals('completada', $this->actividadTrabajador1->estado);
        $this->assertEquals($fechaEjecutada, Carbon::parse($this->actividadTrabajador1->fecha_ejecucion)->toDateString());
        $this->assertEquals($this->trabajador1->id, $this->actividadTrabajador1->ejecutado_por);
    }

    /**
     * Criterio 3: Validación de fecha no futura.
     * Cuando intento registrar una fecha de ejecución futura, el sistema muestra error
     * y no permite guardar.
     */
    public function test_criterio_3_validacion_de_fecha_no_futura(): void
    {
        $fechaFutura = Carbon::now()->addDays(2)->toDateString();

        $response = $this->actingAs($this->trabajador1)->post(route('actividades.completar', $this->actividadTrabajador1), [
            'fecha_ejecucion' => $fechaFutura,
        ]);

        $response->assertSessionHasErrors(['fecha_ejecucion']);

        $this->actividadTrabajador1->refresh();
        $this->assertEquals('pendiente', $this->actividadTrabajador1->estado);
        $this->assertNull($this->actividadTrabajador1->fecha_ejecucion);
    }

    /**
     * Criterio 4: Captura de fotografía como evidencia.
     * Cuando capturo una fotografía como evidencia, el sistema la guarda
     * y la asocia a la actividad realizada.
     */
    public function test_criterio_4_captura_de_fotografia_como_evidencia(): void
    {
        $fotoFake = UploadedFile::fake()->image('evidencia_riego.jpg', 800, 600);

        $response = $this->actingAs($this->trabajador1)->post(route('actividades.completar', $this->actividadTrabajador1), [
            'fotografia' => $fotoFake,
        ]);

        $response->assertRedirect();
        $this->actividadTrabajador1->refresh();

        $this->assertEquals('completada', $this->actividadTrabajador1->estado);
        $this->assertNotNull($this->actividadTrabajador1->fotografia);
        Storage::disk('public')->assertExists($this->actividadTrabajador1->fotografia);
    }

    /**
     * Criterio 5: Fotografía en modo offline.
     * Cuando tomo una fotografía sin conexión a internet, el sistema almacena
     * la imagen/evidencia localmente y la deja pendiente de sincronización.
     */
    public function test_criterio_5_fotografia_en_modo_offline(): void
    {
        $response = $this->actingAs($this->trabajador1)->get(route('cultivos.show', $this->cultivo));

        $response->assertOk();
        $response->assertSee('modalSubirFoto');
        $response->assertSee('sigvos_offline_evidencias_fotos');
        $response->assertSee('Modo Offline: Fotografía conservada localmente en la memoria del dispositivo');
    }

    /**
     * Criterio 6: Consulta del historial fotográfico.
     * Cuando consulto el historial fotográfico del cultivo, el sistema
     * muestra las evidencias fotográficas registradas.
     */
    public function test_criterio_6_consulta_del_historial_fotografico(): void
    {
        // Subir foto para el cultivo
        $fotoPath = 'cultivos/evidencia_test_01.jpg';
        Storage::disk('public')->put($fotoPath, 'fake-image-binary');

        FotoCultivo::create([
            'id_cultivo'  => $this->cultivo->id,
            'id_usuario'  => $this->trabajador1->id,
            'ruta'        => $fotoPath,
            'descripcion' => 'Evidencia de floración en lote principal',
        ]);

        $response = $this->actingAs($this->trabajador1)->get(route('cultivos.show', $this->cultivo));

        $response->assertOk();
        $response->assertSee('Evidencias Visuales');
        $response->assertSee('Evidencia de floración en lote principal');
        $response->assertSee(Storage::url($fotoPath));
    }
}
