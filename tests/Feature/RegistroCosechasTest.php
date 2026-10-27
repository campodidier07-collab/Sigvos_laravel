<?php

namespace Tests\Feature;

use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Usuario;
use App\Models\Variedad;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroCosechasTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Lote $lote;
    protected Variedad $variedad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1, // Admin
            'activo' => true,
        ]);

        $this->variedad = Variedad::first();

        $this->lote = Lote::create([
            'identificador'     => 'LT-101',
            'nombre'            => 'Lote Sector Norte',
            'ubicacion'         => 'Sector Norte Principal',
            'area_ha'           => 4.50,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);
    }

    /**
     * Criterio 1: Registro exitoso de cosecha.
     * Cuando registro la cosecha indicando la cantidad obtenida
     * -> Entonces el sistema marca el cultivo como cosechado y libera el lote.
     */
    public function test_criterio_1_registro_exitoso_de_cosecha(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-TEST-001',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(4)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'estado'                 => 'creciendo',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
            'cantidad_estimada_kg'   => 1000.00,
        ]);

        $response = $this->actingAs($this->admin)->post(route('cosecha.store'), [
            'id_cultivo'            => $cultivo->id,
            'fecha_cosecha_real'    => now()->toDateString(),
            'cantidad_cosechada_kg' => 1250.50,
            'observaciones'         => 'Excelente calidad de fruto',
        ]);

        $response->assertRedirect(route('cosecha.index'));
        $response->assertSessionHas('success');

        $cultivo->refresh();
        $this->assertEquals('cosechado', $cultivo->estado);
        $this->assertEquals(1250.50, $cultivo->cantidad_cosechada_kg);
        $this->assertNull($cultivo->activo_en_lote);

        $this->lote->refresh();
        $this->assertEquals('disponible', $this->lote->estado);
    }

    /**
     * Criterio 2: Lote disponible después de cosecha.
     * Cuando consulto el estado del lote después de la cosecha
     * -> Entonces el sistema muestra el lote como disponible para un nuevo cultivo.
     */
    public function test_criterio_2_lote_disponible_despues_de_cosecha(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-TEST-002',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(4)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'estado'                 => 'creciendo',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
            'cantidad_estimada_kg'   => 800.00,
        ]);

        $this->actingAs($this->admin)->post(route('cosecha.store'), [
            'id_cultivo'            => $cultivo->id,
            'fecha_cosecha_real'    => now()->toDateString(),
            'cantidad_cosechada_kg' => 850.00,
        ]);

        $this->lote->refresh();
        $this->assertEquals('disponible', $this->lote->estado);

        // Verificamos que el lote aparezca como elegible para nuevo cultivo en cultivos.create
        $response = $this->actingAs($this->admin)->get(route('cultivos.create'));
        $response->assertStatus(200);
        $response->assertSee($this->lote->nombre);
        $response->assertSee('LT-101');
    }

    /**
     * Criterio 3: Historial de producción por lote.
     * Cuando consulto el historial de cosechas de un lote
     * -> Entonces el sistema muestra las cosechas registradas en ese lote.
     */
    public function test_criterio_3_historial_de_produccion_por_lote(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-HIST-003',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(6)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'fecha_cosecha_real'     => now()->toDateString(),
            'estado'                 => 'cosechado',
            'activo_en_lote'         => null,
            'registrado_por'         => $this->admin->id,
            'cantidad_cosechada_kg'  => 920.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('lotes.show', $this->lote->id));

        $response->assertStatus(200);
        $response->assertSee('CUL-HIST-003');
        $response->assertSee('920.00 Kg');
    }

    /**
     * Criterio 4: Validación de cantidad mínima.
     * Cuando intento guardar una cantidad menor a la permitida (0 o negativa)
     * -> Entonces el sistema muestra un error y no permite continuar.
     */
    public function test_criterio_4_validacion_de_cantidad_minima(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-VAL-004',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(4)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'estado'                 => 'creciendo',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
            'cantidad_estimada_kg'   => 500.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('cosecha.index'))
            ->post(route('cosecha.store'), [
                'id_cultivo'            => $cultivo->id,
                'fecha_cosecha_real'    => now()->toDateString(),
                'cantidad_cosechada_kg' => 0,
            ]);

        $response->assertRedirect(route('cosecha.index'));
        $response->assertSessionHasErrors('cantidad_cosechada_kg');

        $cultivo->refresh();
        $this->assertEquals('creciendo', $cultivo->estado);
        $this->assertEquals($this->lote->id, $cultivo->activo_en_lote);
    }

    /**
     * Criterio 5: Registro de cosecha con observaciones.
     * Cuando ingreso observaciones al registrar la cosecha
     * -> Entonces el sistema guarda las observaciones asociadas a la cosecha.
     */
    public function test_criterio_5_registro_de_cosecha_con_observaciones(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-OBS-005',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(5)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'estado'                 => 'creciendo',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
            'cantidad_estimada_kg'   => 600.00,
        ]);

        $observacionesTexto = 'Cosecha de exportación clasificada grado A, sin plagas ni hongos detectados.';

        $response = $this->actingAs($this->admin)->post(route('cosecha.store'), [
            'id_cultivo'            => $cultivo->id,
            'fecha_cosecha_real'    => now()->toDateString(),
            'cantidad_cosechada_kg' => 640.00,
            'observaciones'         => $observacionesTexto,
        ]);

        $response->assertRedirect(route('cosecha.index'));

        $cultivo->refresh();
        $this->assertEquals($observacionesTexto, $cultivo->observaciones);
    }

    /**
     * Criterio 6: Comparación con estimación inicial.
     * Cuando se completa el registro de cosecha
     * -> Entonces el sistema muestra la diferencia entre la cosecha estimada y la obtenida.
     */
    public function test_criterio_6_comparacion_con_estimacion_inicial(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-EST-006',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(6)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'estado'                 => 'creciendo',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
            'cantidad_estimada_kg'   => 500.00,
        ]);

        $response = $this->actingAs($this->admin)->post(route('cosecha.store'), [
            'id_cultivo'            => $cultivo->id,
            'fecha_cosecha_real'    => now()->toDateString(),
            'cantidad_cosechada_kg' => 550.00,
        ]);

        $response->assertRedirect(route('cosecha.index'));
        $response->assertSessionHas('success');
        $successMessage = session('success');
        $this->assertStringContainsString('+50 kg', $successMessage);

        // Verificamos visualmente en el índice de cosechas
        $viewResponse = $this->actingAs($this->admin)->get(route('cosecha.index'));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('+50 Kg');
    }

    /**
     * Hardening DP-17 + DP-09: Auto-cierre de actividades huérfanas al cosechar.
     */
    public function test_cierre_de_actividades_huerfanas_al_registrar_cosecha(): void
    {
        $cultivo = Cultivo::create([
            'codigo'                 => 'CUL-ACT-007',
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $this->variedad->id,
            'fecha_siembra'          => now()->subMonths(5)->toDateString(),
            'fecha_cosecha_estimada' => now()->toDateString(),
            'estado'                 => 'creciendo',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
            'cantidad_estimada_kg'   => 800.00,
        ]);

        $actividad = \App\Models\Actividad::create([
            'id_cultivo'        => $cultivo->id,
            'id_tipo_actividad' => 1,
            'asignado_a'        => $this->admin->id,
            'creado_por'        => $this->admin->id,
            'fecha_programada'  => now()->addDays(2)->toDateString(),
            'estado'            => 'pendiente',
            'descripcion'       => 'Poda de mantenimiento de cultivo',
        ]);

        $response = $this->actingAs($this->admin)->post(route('cosecha.store'), [
            'id_cultivo'            => $cultivo->id,
            'fecha_cosecha_real'    => now()->toDateString(),
            'cantidad_cosechada_kg' => 850.00,
        ]);

        $response->assertRedirect(route('cosecha.index'));

        $actividad->refresh();
        $this->assertEquals('cancelada', $actividad->estado);
        $this->assertStringContainsString('Cancelada automáticamente', $actividad->observaciones);
    }
}
