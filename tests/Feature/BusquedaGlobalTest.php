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

class BusquedaGlobalTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $worker;
    protected Lote $loteA;
    protected Lote $loteB;
    protected Variedad $variedadMaiz;
    protected Variedad $variedadCafe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1,
            'activo' => true,
        ]);

        $this->worker = Usuario::factory()->create([
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->loteA = Lote::create([
            'identificador'     => 'A',
            'nombre'            => 'Lote San José',
            'ubicacion'         => 'Sector Norte',
            'area_ha'           => 4.00,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $this->loteB = Lote::create([
            'identificador'     => 'B',
            'nombre'            => 'Lote Las Margaritas',
            'ubicacion'         => 'Sector Sur',
            'area_ha'           => 2.50,
            'id_tipo_preferido' => 2,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $this->variedadMaiz = Variedad::where('nombre', 'like', '%Maíz%')->first() ?? Variedad::first();
        $this->variedadCafe = Variedad::where('nombre', 'like', '%Café%')->orWhere('nombre', 'like', '%Arábica%')->first() ?? Variedad::skip(1)->first();
    }

    /**
     * Criterio 1: Búsqueda rápida por nombre de cultivo
     * Dado que necesito encontrar información de un cultivo específico,
     * cuando realizo la búsqueda por nombre,
     * entonces el sistema debe mostrar los resultados relacionados.
     */
    public function test_criterio_1_busqueda_rapida_por_nombre_de_cultivo(): void
    {
        $cultivo1 = Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-MAIZ-2026',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(115),
            'activo_en_lote'         => $this->loteA->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $cultivo2 = Cultivo::create([
            'id_lote'                => $this->loteB->id,
            'id_variedad'            => $this->variedadCafe->id,
            'codigo'                 => 'CUL-CAFE-2026',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(270),
            'activo_en_lote'         => $this->loteB->id,
            'registrado_por'         => $this->admin->id,
        ]);

        // 1a. Vista web
        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index', ['q' => 'CUL-MAIZ']));

        $response->assertOk();
        $response->assertSee('CUL-MAIZ-2026');
        $response->assertDontSee('CUL-CAFE-2026');

        // 1b. API de búsqueda rápida
        $responseApi = $this->actingAs($this->admin)
            ->getJson(route('busqueda.rapida', ['q' => 'CUL-MAIZ']));

        $responseApi->assertOk();
        $this->assertEquals(1, count($responseApi->json('cultivos')));
        $this->assertEquals('CUL-MAIZ-2026', $responseApi->json('cultivos.0.codigo'));
    }

    /**
     * Criterio 2: Búsqueda por rango de fechas
     * Dado que necesito revisar actividades de un período específico,
     * cuando aplico un rango de fechas,
     * entonces el sistema debe filtrar la información correspondiente.
     */
    public function test_criterio_2_busqueda_por_rango_de_fechas(): void
    {
        $cultivo = Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-DATE-001',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => $this->loteA->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $tipoActividad = TipoActividad::first();

        // Actividad en rango (la próxima semana)
        $actEnRango = Actividad::create([
            'id_cultivo'        => $cultivo->id,
            'id_tipo_actividad' => $tipoActividad->id,
            'descripcion'       => 'Fertilización programada en rango',
            'estado'            => 'pendiente',
            'fecha_programada'  => Carbon::today()->addDays(3),
            'asignado_a'        => $this->worker->id,
            'creado_por'        => $this->admin->id,
        ]);

        // Actividad fuera de rango (en 40 días)
        $actFueraRango = Actividad::create([
            'id_cultivo'        => $cultivo->id,
            'id_tipo_actividad' => $tipoActividad->id,
            'descripcion'       => 'Poda futura fuera de rango',
            'estado'            => 'pendiente',
            'fecha_programada'  => Carbon::today()->addDays(40),
            'asignado_a'        => $this->worker->id,
            'creado_por'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index', [
                'fecha_desde' => Carbon::today()->toDateString(),
                'fecha_hasta' => Carbon::today()->addDays(7)->toDateString(),
                'tipo'        => 'actividades',
            ]));

        $response->assertOk();
        $response->assertSee('Fertilización programada en rango');
        $response->assertDontSee('Poda futura fuera de rango');
    }

    /**
     * Criterio 3: Filtro por lote específico
     * Dado que necesito consultar la información de un lote en particular,
     * cuando aplico el filtro por lote,
     * entonces el sistema debe mostrar solo lo relacionado con ese lote.
     */
    public function test_criterio_3_filtro_por_lote_especifico(): void
    {
        // Cultivo en lote A
        Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-LOTE-A',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => $this->loteA->id,
            'registrado_por'         => $this->admin->id,
        ]);

        // Cultivo en lote B
        Cultivo::create([
            'id_lote'                => $this->loteB->id,
            'id_variedad'            => $this->variedadCafe->id,
            'codigo'                 => 'CUL-LOTE-B',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(200),
            'activo_en_lote'         => $this->loteB->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index', [
                'id_lote' => $this->loteA->id,
            ]));

        $response->assertOk();
        $response->assertSee('CUL-LOTE-A');
        $response->assertDontSee('CUL-LOTE-B');
    }

    /**
     * Criterio 4: Filtro combinado múltiple
     * Dado que necesito encontrar información más específica,
     * cuando aplico varios filtros al mismo tiempo,
     * entonces el sistema debe mostrar solo los resultados coincidentes.
     */
    public function test_criterio_4_filtro_combinado_multiple(): void
    {
        // Cultivo que cumple TODO: Lote A, estado sembrado, variedad Maiz, en fecha
        Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-MATCH-ALL',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => $this->loteA->id,
            'registrado_por'         => $this->admin->id,
        ]);

        // Cultivo en Lote A pero con estado cosechado (no cumple estado)
        Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-FAIL-STATUS',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => null,
            'registrado_por'         => $this->admin->id,
        ]);

        // Cultivo con estado sembrado pero en Lote B (no cumple lote)
        Cultivo::create([
            'id_lote'                => $this->loteB->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-FAIL-LOTE',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => $this->loteB->id,
            'registrado_por'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index', [
                'q'           => 'CUL-',
                'id_lote'     => $this->loteA->id,
                'estado'      => 'sembrado',
                'fecha_desde' => Carbon::today()->subDay()->toDateString(),
                'fecha_hasta' => Carbon::today()->addDay()->toDateString(),
            ]));

        $response->assertOk();
        $response->assertSee('CUL-MATCH-ALL');
        $response->assertDontSee('CUL-FAIL-STATUS');
        $response->assertDontSee('CUL-FAIL-LOTE');
    }

    /**
     * Criterio 5: Filtro por estado de cultivo
     * Dado que necesito consultar cultivos según su estado,
     * cuando aplico el filtro correspondiente,
     * entonces el sistema debe mostrar los cultivos que coincidan.
     */
    public function test_criterio_5_filtro_por_estado_de_cultivo(): void
    {
        Cultivo::create([
            'id_lote'                => $this->loteA->id,
            'id_variedad'            => $this->variedadMaiz->id,
            'codigo'                 => 'CUL-SEMBRADO-1',
            'estado'                 => 'sembrado',
            'fecha_siembra'          => Carbon::today(),
            'fecha_cosecha_estimada' => Carbon::today()->addDays(100),
            'activo_en_lote'         => $this->loteA->id,
            'registrado_por'         => $this->admin->id,
        ]);

        Cultivo::create([
            'id_lote'                => $this->loteB->id,
            'id_variedad'            => $this->variedadCafe->id,
            'codigo'                 => 'CUL-COSECHADO-1',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => Carbon::today()->subDays(200),
            'fecha_cosecha_estimada' => Carbon::today()->subDays(10),
            'fecha_cosecha_real'     => Carbon::today()->subDays(8),
            'cantidad_cosechada_kg'  => 500,
            'activo_en_lote'         => null,
            'registrado_por'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index', ['estado' => 'sembrado', 'tipo' => 'cultivos']));

        $response->assertOk();
        $response->assertSee('CUL-SEMBRADO-1');
        $response->assertDontSee('CUL-COSECHADO-1');
    }

    /**
     * Criterio 6: Búsqueda sin resultados
     * Dado que realizo una búsqueda sin coincidencias,
     * cuando ingreso un término no registrado,
     * entonces el sistema debe informarlo claramente.
     */
    public function test_criterio_6_busqueda_sin_resultados(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index', ['q' => 'TERMINO_TOTALMENTE_INEXISTENTE_9999']));

        $response->assertOk();
        $response->assertSee('No se encontraron resultados');
        $response->assertSee('Resultados de la búsqueda (0)');
    }

    /**
     * Criterio 7: Historial de búsquedas recientes
     * Dado que realizo búsquedas con frecuencia,
     * cuando abro el buscador,
     * entonces el sistema debe mostrar el historial de búsquedas recientes.
     */
    public function test_criterio_7_historial_de_busquedas_recientes(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('busqueda.index'));

        $response->assertOk();
        $response->assertSee('seccionHistorial');
        $response->assertSee('Búsquedas Recientes');
        $response->assertSee('contenedorHistorial');
        $response->assertSee('sigvos_busquedas_recientes');
    }
}
