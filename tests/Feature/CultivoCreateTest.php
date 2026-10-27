<?php

namespace Tests\Feature;

use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Usuario;
use App\Models\Variedad;
use Carbon\Carbon;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CultivoCreateTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $worker;
    protected Lote $loteDisponible;

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

        $this->loteDisponible = Lote::create([
            'identificador'     => 'A',
            'nombre'            => 'Lote San José',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 3.00,
            'id_tipo_preferido' => 1,
            'estado'            => 'disponible',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);
    }

    /**
     * Criterio 1: Registro de cultivo con asignación de lote exitosa
     * Cuando registro un cultivo indicando tipo, variedad, fecha de siembra y selecciono un lote
     * -> Entonces el sistema crea el cultivo y lo asigna al lote seleccionado.
     */
    public function test_criterio_1_registro_de_cultivo_con_asignacion_de_lote_exitosa(): void
    {
        $variedad = Variedad::first();
        $fechaSiembra = Carbon::today()->toDateString();
        $fechaCosecha = Carbon::today()->addDays($variedad->dias_cosecha_promedio)->toDateString();

        $response = $this->actingAs($this->admin)->post(route('cultivos.store'), [
            'id_lote'                => $this->loteDisponible->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CULT-TEST-001',
            'fecha_siembra'          => $fechaSiembra,
            'fecha_cosecha_estimada' => $fechaCosecha,
            'observaciones'          => 'Siembra inicial exitosa',
        ]);

        // Verifica redirección y confirmación
        $cultivo = Cultivo::where('codigo', 'CULT-TEST-001')->first();
        $this->assertNotNull($cultivo);
        $response->assertRedirect(route('cultivos.show', $cultivo));
        $response->assertSessionHas('success', 'Cultivo registrado exitosamente.');

        // Verifica asignación al lote y actualización de estado
        $this->assertEquals($this->loteDisponible->id, $cultivo->id_lote);
        $this->assertEquals($this->loteDisponible->id, $cultivo->activo_en_lote);

        $this->loteDisponible->refresh();
        $this->assertEquals('ocupado', $this->loteDisponible->estado);
    }

    /**
     * Criterio 2: Cálculo automático de fecha de cosecha para maíz
     * Cuando especifico la fecha de siembra para maíz
     * -> Entonces el sistema calcula automáticamente la fecha estimada de cosecha.
     */
    public function test_criterio_2_calculo_automatico_de_fecha_de_cosecha_para_maiz(): void
    {
        // Obtener variedad Maíz Amarillo (dias_cosecha_promedio = 115)
        $variedadMaiz = Variedad::where('nombre', 'like', '%Maíz Amarillo%')->firstOrFail();
        $this->assertEquals(115, $variedadMaiz->dias_cosecha_promedio);

        $fechaSiembra = Carbon::parse('2026-04-01');
        $fechaEsperada = Carbon::parse('2026-04-01')->addDays(115)->toDateString(); // '2026-07-25'

        // 1. Verificación en la vista: el selector contiene data-dias para cálculo en frontend
        $viewResponse = $this->actingAs($this->admin)->get(route('cultivos.create'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('data-dias="115"', false);
        $viewResponse->assertSee('Cálculo automático para');

        // 2. Verificación en backend: si no se envía fecha estimada o viene vacía, el sistema la calcula automáticamente
        $response = $this->actingAs($this->admin)->post(route('cultivos.store'), [
            'id_lote'                => $this->loteDisponible->id,
            'id_variedad'            => $variedadMaiz->id,
            'fecha_siembra'          => $fechaSiembra->toDateString(),
            'fecha_cosecha_estimada' => null, // Omitida intencionalmente para disparar auto-cálculo
        ]);

        $cultivo = Cultivo::where('id_variedad', $variedadMaiz->id)->firstOrFail();
        $this->assertEquals($fechaEsperada, $cultivo->fecha_cosecha_estimada->toDateString());
    }

    /**
     * Criterio 3: Cálculo automático de fecha de cosecha para café arábica
     * Cuando especifico la fecha de siembra para café arábica
     * -> Entonces el sistema calcula automáticamente la fecha estimada de cosecha.
     */
    public function test_criterio_3_calculo_automatico_de_fecha_de_cosecha_para_cafe_arabica(): void
    {
        // Obtener variedad Café Arábica (dias_cosecha_promedio = 270)
        $variedadCafe = Variedad::where('nombre', 'Arábica')->firstOrFail();
        $this->assertEquals(270, $variedadCafe->dias_cosecha_promedio);

        $fechaSiembra = Carbon::parse('2026-01-15');
        $fechaEsperada = Carbon::parse('2026-01-15')->addDays(270)->toDateString(); // '2026-10-12'

        // 1. Verificación en la vista: selector incluye data-dias="270"
        $viewResponse = $this->actingAs($this->admin)->get(route('cultivos.create'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('data-dias="270"', false);

        // 2. Verificación en backend: auto-cálculo automático exacto
        $response = $this->actingAs($this->admin)->post(route('cultivos.store'), [
            'id_lote'                => $this->loteDisponible->id,
            'id_variedad'            => $variedadCafe->id,
            'fecha_siembra'          => $fechaSiembra->toDateString(),
            'fecha_cosecha_estimada' => null,
        ]);

        $cultivo = Cultivo::where('id_variedad', $variedadCafe->id)->firstOrFail();
        $this->assertEquals($fechaEsperada, $cultivo->fecha_cosecha_estimada->toDateString());
    }

    /**
     * Criterio 4: Validación de lote disponible
     * Cuando selecciono un lote en estado ocupado o en descanso
     * -> Entonces el sistema muestra un error y no permite continuar con el registro.
     */
    public function test_criterio_4_validacion_de_lote_no_disponible_ocupado_o_en_descanso(): void
    {
        $variedad = Variedad::first();

        // Caso A: Lote ocupado
        $loteOcupado = Lote::create([
            'identificador'  => 'B',
            'nombre'         => 'Lote Los Naranjos',
            'ubicacion'      => 'Sector Sur',
            'area_ha'        => 2.50,
            'estado'         => 'ocupado',
            'activo'         => true,
            'es_alternativo' => false,
        ]);

        $responseOcupado = $this->actingAs($this->admin)->post(route('cultivos.store'), [
            'id_lote'       => $loteOcupado->id,
            'id_variedad'   => $variedad->id,
            'fecha_siembra' => Carbon::today()->toDateString(),
        ]);

        $responseOcupado->assertSessionHasErrors('id_lote');
        $this->assertDatabaseMissing('cultivos', ['id_lote' => $loteOcupado->id]);

        // Caso B: Lote en descanso
        $loteEnDescanso = Lote::create([
            'identificador'  => 'C',
            'nombre'         => 'Lote El Manantial',
            'ubicacion'      => 'Sector Este',
            'area_ha'        => 4.00,
            'estado'         => 'en_descanso',
            'activo'         => true,
            'es_alternativo' => false,
        ]);

        $responseDescanso = $this->actingAs($this->admin)->post(route('cultivos.store'), [
            'id_lote'       => $loteEnDescanso->id,
            'id_variedad'   => $variedad->id,
            'fecha_siembra' => Carbon::today()->toDateString(),
        ]);

        $responseDescanso->assertSessionHasErrors('id_lote');
        $this->assertDatabaseMissing('cultivos', ['id_lote' => $loteEnDescanso->id]);

        // Caso C: En el formulario de creación, los lotes ocupados o en descanso no aparecen como opciones seleccionables
        $createResponse = $this->actingAs($this->admin)->get(route('cultivos.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Lote A - Lote San José');
        $createResponse->assertDontSee('Lote B - Lote Los Naranjos');
        $createResponse->assertDontSee('Lote C - Lote El Manantial');
    }
}
