<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lote;
use App\Models\Cultivo;
use App\Models\Variedad;
use App\Models\TipoActividad;
use App\Models\Actividad;
use App\Models\AsignacionLote;
use Carbon\Carbon;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistroActividadesTrabajadorTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $trabajadorAsignado;
    protected Usuario $trabajadorNoAsignado;
    protected Lote $loteAsignado;
    protected Lote $loteAjeno;
    protected Cultivo $cultivoActivo;
    protected Cultivo $cultivoFinalizado;
    protected Cultivo $cultivoAjeno;
    protected TipoActividad $tipoFertilizacion;
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

        $this->trabajadorAsignado = Usuario::factory()->create([
            'nombre' => 'Carlos Operador',
            'email'  => 'carlos_op@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->trabajadorNoAsignado = Usuario::factory()->create([
            'nombre' => 'Luis Externo',
            'email'  => 'luis_ext@sigvos.test',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->loteAsignado = Lote::create([
            'identificador'     => 'DP14-L1',
            'nombre'            => 'Lote Asignado A',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 4.5,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $this->loteAjeno = Lote::create([
            'identificador'     => 'DP14-L2',
            'nombre'            => 'Lote Ajeno B',
            'ubicacion'         => 'Sector Sur',
            'area_ha'           => 3.0,
            'id_tipo_preferido' => 2,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        // Asignar Lote 1 a $trabajadorAsignado
        AsignacionLote::create([
            'id_usuario'   => $this->trabajadorAsignado->id,
            'id_lote'      => $this->loteAsignado->id,
            'asignado_por' => $this->admin->id,
            'activo'       => true,
            'clave_activa' => "lote_{$this->loteAsignado->id}_user_{$this->trabajadorAsignado->id}",
            'creado_en'    => now(),
        ]);

        $variedadCafe = Variedad::where('nombre', 'Castillo')->first() ?? Variedad::first();

        // Cultivo Activo en lote asignado
        $this->cultivoActivo = Cultivo::create([
            'id_lote'                => $this->loteAsignado->id,
            'id_variedad'            => $variedadCafe->id,
            'codigo'                 => 'CUL-DP14-ACTIVO',
            'fecha_siembra'          => Carbon::now()->subMonths(4)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addMonths(2)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->loteAsignado->id,
            'registrado_por'         => $this->admin->id,
        ]);

        // Cultivo Finalizado (cosechado) en lote asignado
        $this->cultivoFinalizado = Cultivo::create([
            'id_lote'                => $this->loteAsignado->id,
            'id_variedad'            => $variedadCafe->id,
            'codigo'                 => 'CUL-DP14-FIN',
            'fecha_siembra'          => Carbon::now()->subMonths(10)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->subMonth()->toDateString(),
            'fecha_cosecha_real'     => Carbon::now()->subWeeks(2)->toDateString(),
            'cantidad_cosechada_kg'  => 1200.50,
            'estado'                 => 'cosechado',
            'activo_en_lote'         => null,
            'registrado_por'         => $this->admin->id,
        ]);

        // Cultivo en lote ajeno (no asignado al trabajador)
        $this->cultivoAjeno = Cultivo::create([
            'id_lote'                => $this->loteAjeno->id,
            'id_variedad'            => $variedadCafe->id,
            'codigo'                 => 'CUL-DP14-AJENO',
            'fecha_siembra'          => Carbon::now()->subMonths(2)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addMonths(4)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->loteAjeno->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $this->tipoFertilizacion = TipoActividad::where('codigo', 'FERTILIZACION')->first();
        $this->tipoPoda          = TipoActividad::where('codigo', 'PODA')->first();
    }

    /**
     * Criterio 1: Registro exitoso de actividad realizada.
     * Dado que estoy trabajando sobre un cultivo asignado, cuando registro una actividad
     * indicando tipo y descripción, entonces el sistema guarda la actividad y muestra confirmación.
     */
    public function test_criterio_1_registro_exitoso_de_actividad_realizada(): void
    {
        $response = $this->actingAs($this->trabajadorAsignado)->post(route('actividades.registrar-realizada'), [
            'id_cultivo'        => $this->cultivoActivo->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'descripcion'       => 'Fertilización con urea foliar al 2% en toda la parcela asignada.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actividades', [
            'id_cultivo'        => $this->cultivoActivo->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'creado_por'        => $this->trabajadorAsignado->id,
            'estado'            => 'completada',
        ]);
    }

    /**
     * Criterio 2: Validación de descripción mínima.
     * Dado que deseo guardar una actividad realizada, cuando ingreso una descripción
     * vacía o muy corta, entonces el sistema debe validarla y no permitir continuar.
     */
    public function test_criterio_2_validacion_de_descripcion_minima(): void
    {
        $response = $this->actingAs($this->trabajadorAsignado)->post(route('actividades.registrar-realizada'), [
            'id_cultivo'        => $this->cultivoActivo->id,
            'id_tipo_actividad' => $this->tipoPoda->id,
            'descripcion'       => 'Poda', // Menor a 10 caracteres
        ]);

        $response->assertSessionHasErrors(['descripcion']);
        $this->assertDatabaseMissing('actividades', [
            'descripcion' => 'Poda',
        ]);
    }

    /**
     * Criterio 3: Restricción sobre cultivos finalizados.
     * Dado que un cultivo ya fue finalizado, cuando intento registrar una nueva actividad,
     * entonces el sistema rechaza el registro y muestra una advertencia.
     */
    public function test_criterio_3_restriccion_sobre_cultivos_finalizados(): void
    {
        $response = $this->actingAs($this->trabajadorAsignado)->post(route('actividades.registrar-realizada'), [
            'id_cultivo'        => $this->cultivoFinalizado->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'descripcion'       => 'Intento de fertilización en un cultivo que ya fue cosechado.',
        ]);

        $response->assertSessionHasErrors(['id_cultivo']);
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('actividades', [
            'descripcion' => 'Intento de fertilización en un cultivo que ya fue cosechado.',
        ]);
    }

    /**
     * Criterio 4: Asignación automática de fecha de ejecución.
     * Dado que estoy registrando una actividad ya ejecutada, cuando no ingreso la fecha,
     * entonces el sistema le asigna la fecha actual automáticamente.
     */
    public function test_criterio_4_asignacion_automatica_de_fecha_de_ejecucion(): void
    {
        $hoy = Carbon::today()->toDateString();

        $response = $this->actingAs($this->trabajadorAsignado)->post(route('actividades.registrar-realizada'), [
            'id_cultivo'        => $this->cultivoActivo->id,
            'id_tipo_actividad' => $this->tipoPoda->id,
            'descripcion'       => 'Poda fitosanitaria de mantenimiento en hileras este.',
            // Se omite intencionalmente 'fecha_realizacion'
        ]);

        $response->assertSessionHasNoErrors();

        $actividad = Actividad::where('id_cultivo', $this->cultivoActivo->id)
            ->where('descripcion', 'Poda fitosanitaria de mantenimiento en hileras este.')
            ->first();

        $this->assertNotNull($actividad);
        $this->assertEquals($hoy, Carbon::parse($actividad->fecha_ejecucion)->toDateString());
        $this->assertEquals($hoy, Carbon::parse($actividad->fecha_programada)->toDateString());
    }

    /**
     * Criterio 5: Bloqueo en cultivos no asignados.
     * Dado que existen cultivos asignados a otros trabajadores o sin asignar al usuario actual,
     * cuando intento registrar una actividad en uno que no me corresponde,
     * entonces el sistema restringe el acceso y muestra que no tengo permisos (403).
     */
    public function test_criterio_5_bloqueo_en_cultivos_no_asignados(): void
    {
        // 5a. Trabajador intenta registrar en un cultivo de un lote no asignado a él
        $response = $this->actingAs($this->trabajadorAsignado)->post(route('actividades.registrar-realizada'), [
            'id_cultivo'        => $this->cultivoAjeno->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'descripcion'       => 'Intento fraudulento de labor en lote ajeno.',
        ]);

        $response->assertForbidden();

        // 5b. Trabajador externo sin asignaciones intenta registrar en cultivo activo
        $responseExterno = $this->actingAs($this->trabajadorNoAsignado)->post(route('actividades.registrar-realizada'), [
            'id_cultivo'        => $this->cultivoActivo->id,
            'id_tipo_actividad' => $this->tipoFertilizacion->id,
            'descripcion'       => 'Intento de registro sin tener lotes asignados.',
        ]);

        $responseExterno->assertForbidden();

        $this->assertDatabaseMissing('actividades', [
            'descripcion' => 'Intento fraudulento de labor en lote ajeno.',
        ]);
    }

    /**
     * Criterio 6: Registro offline pendiente de sincronización.
     * Dado que no tengo conectividad en campo, cuando guardo una actividad realizada,
     * el sistema cuenta con soporte de persistencia local en caché y encolamiento.
     */
    public function test_criterio_6_registro_offline_soporte_interfaz(): void
    {
        $response = $this->actingAs($this->trabajadorAsignado)->get(route('cultivos.show', $this->cultivoActivo));

        $response->assertOk();
        $response->assertSee('modalRegistrarLaborRealizada');
        $response->assertSee('sigvos_offline_actividades_realizadas');
        $response->assertSee('Sin conexión a internet: La actividad ha sido guardada localmente');
    }
}
