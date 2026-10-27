<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\TipoCultivo;
use App\Models\Usuario;
use App\Models\Variedad;
use Carbon\Carbon;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesProductividadTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Usuario $worker;
    protected Lote $lote1;
    protected Lote $lote2;
    protected TipoCultivo $tipoAguacate;
    protected TipoCultivo $tipoCafe;
    protected Variedad $variedadHass;
    protected Variedad $variedadArabica;

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

        $this->tipoAguacate = TipoCultivo::firstOrCreate(
            ['codigo' => 'AGUACATE'],
            ['nombre' => 'Aguacate', 'descripcion' => 'Cultivo de aguacate', 'activo' => true]
        );

        $this->tipoCafe = TipoCultivo::firstOrCreate(
            ['codigo' => 'CAFE'],
            ['nombre' => 'Café', 'descripcion' => 'Cultivo de café', 'activo' => true]
        );

        $this->variedadHass = Variedad::firstOrCreate(
            ['id_tipo_cultivo' => $this->tipoAguacate->id, 'nombre' => 'Aguacate Hass'],
            ['dias_cosecha_min' => 180, 'dias_cosecha_max' => 240, 'dias_cosecha_promedio' => 210, 'activo' => true]
        );

        $this->variedadArabica = Variedad::firstOrCreate(
            ['id_tipo_cultivo' => $this->tipoCafe->id, 'nombre' => 'Café Arábica'],
            ['dias_cosecha_min' => 200, 'dias_cosecha_max' => 300, 'dias_cosecha_promedio' => 250, 'activo' => true]
        );

        $this->lote1 = Lote::create([
            'identificador'     => 'LOT-01',
            'nombre'            => 'Lote Colina',
            'area_ha'           => 2.0,
            'ubicacion'         => 'Sector Norte',
            'id_tipo_preferido' => $this->tipoAguacate->id,
            'estado'            => 'ocupado',
            'activo'            => true,
        ]);

        $this->lote2 = Lote::create([
            'identificador'     => 'LOT-02',
            'nombre'            => 'Lote Valle',
            'area_ha'           => 4.0,
            'ubicacion'         => 'Sector Sur',
            'id_tipo_preferido' => $this->tipoCafe->id,
            'estado'            => 'ocupado',
            'activo'            => true,
        ]);
    }

    /**
     * Criterio 1: Generación de reportes agrícolas según tipo de análisis.
     * Permite generar reportes de productividad entre lotes, actividades por periodo o próximas cosechas.
     */
    public function test_criterio_1_generacion_de_reportes_agricolas_segun_tipo_de_analisis(): void
    {
        // 1. Reporte de productividad entre lotes (default o explícito)
        $respProd = $this->actingAs($this->admin)->get(route('reportes.index', ['tipo_reporte' => 'productividad_lotes']));
        $respProd->assertOk();
        $respProd->assertSee('Productividad entre Lotes');
        $respProd->assertSee('Tabla Comparativa de Eficiencia y Rendimiento de Lotes');

        // 2. Reporte de actividades por periodo
        $respAct = $this->actingAs($this->admin)->get(route('reportes.index', ['tipo_reporte' => 'actividades_periodo']));
        $respAct->assertOk();
        $respAct->assertSee('Actividades por Periodo');
        $respAct->assertSee('Detalle de Actividades Operativas');

        // 3. Reporte de próximas cosechas
        $respCosechas = $this->actingAs($this->admin)->get(route('reportes.index', ['tipo_reporte' => 'proximas_cosechas']));
        $respCosechas->assertOk();
        $respCosechas->assertSee('Próximas Cosechas');
        $respCosechas->assertSee('Cronograma de Cosechas Estimadas');
    }

    /**
     * Criterio 4: Exportación de reportes a PDF.
     * El sistema contiene el botón "Exportar a PDF", el contenedor captureble y las librerías jsPDF y html2canvas.
     */
    public function test_criterio_4_exportacion_de_reportes_a_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.index'));
        $response->assertOk();
        $response->assertSee('id="exportPdfBtn"', false);
        $response->assertSee('Exportar a PDF');
        $response->assertSee('id="reportes-container"', false);
        $response->assertSee('html2canvas.min.js', false);
        $response->assertSee('jspdf.umd.min.js', false);
        $response->assertSee('Reporte_Oficial_SIGVOS.pdf', false);
    }

    /**
     * Criterio 5: Filtrado de reportes por tipo de cultivo.
     * Al aplicar el filtro por tipo de cultivo, el sistema muestra únicamente los datos del cultivo seleccionado.
     */
    public function test_criterio_5_filtrado_de_reportes_por_tipo_de_cultivo(): void
    {
        // Cosecha en Lote 1 de Aguacate (5,000 kg en 2 ha = 2,500 kg/ha)
        Cultivo::create([
            'id_lote'                => $this->lote1->id,
            'id_variedad'            => $this->variedadHass->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-AGU-01',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => Carbon::now()->subMonths(8),
            'fecha_cosecha_estimada' => Carbon::now()->subMonth(),
            'fecha_cosecha_real'     => Carbon::now()->subDays(10),
            'cantidad_cosechada_kg'  => 5000.0,
            'activo_en_lote'         => null,
        ]);

        // Cosecha en Lote 2 de Café (2,000 kg en 4 ha = 500 kg/ha)
        Cultivo::create([
            'id_lote'                => $this->lote2->id,
            'id_variedad'            => $this->variedadArabica->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-CAF-01',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => Carbon::now()->subMonths(9),
            'fecha_cosecha_estimada' => Carbon::now()->subMonth(),
            'fecha_cosecha_real'     => Carbon::now()->subDays(5),
            'cantidad_cosechada_kg'  => 2000.0,
            'activo_en_lote'         => null,
        ]);

        // Filtrar exclusivamente por Aguacate
        $response = $this->actingAs($this->admin)->get(route('reportes.index', [
            'tipo_reporte'    => 'productividad_lotes',
            'id_tipo_cultivo' => $this->tipoAguacate->id,
        ]));

        $response->assertOk();
        $response->assertSee('LOT-01');
        $response->assertSee('Lote Colina');
        $response->assertSee('5,000.00'); // Total cosechado lote 1
        $response->assertSee('2,500.00'); // Rendimiento Kg/Ha lote 1
        // Lote 2 (Café) no debe figurar en el análisis filtrado por Aguacate
        $response->assertDontSee('LOT-02');
        $response->assertDontSee('Lote Valle');
    }

    /**
     * Criterio 6: Métricas clave en reportes de productividad.
     * Muestra indicadores principales: Rendimiento Promedio (Kg/Ha), Producción Total, Lote Más Productivo, etc.
     */
    public function test_criterio_6_metricas_clave_en_reportes(): void
    {
        // Lote 1: 2.0 Ha -> 6,000 kg -> 3,000 Kg/Ha (Lote Líder)
        Cultivo::create([
            'id_lote'                => $this->lote1->id,
            'id_variedad'            => $this->variedadHass->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-AGU-02',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => Carbon::now()->subMonths(7),
            'fecha_cosecha_estimada' => Carbon::now()->subDays(15),
            'fecha_cosecha_real'     => Carbon::now()->subDays(12),
            'cantidad_cosechada_kg'  => 6000.0,
            'activo_en_lote'         => null,
        ]);

        // Lote 2: 4.0 Ha -> 4,000 kg -> 1,000 Kg/Ha
        Cultivo::create([
            'id_lote'                => $this->lote2->id,
            'id_variedad'            => $this->variedadArabica->id,
            'registrado_por'         => $this->admin->id,
            'codigo'                 => 'CUL-CAF-02',
            'estado'                 => 'cosechado',
            'fecha_siembra'          => Carbon::now()->subMonths(9),
            'fecha_cosecha_estimada' => Carbon::now()->subDays(20),
            'fecha_cosecha_real'     => Carbon::now()->subDays(18),
            'cantidad_cosechada_kg'  => 4000.0,
            'activo_en_lote'         => null,
        ]);

        // Total general = 10,000 Kg; Total Ha = 6.0 Ha; Rendimiento promedio = 10000 / 6 = 1666.67 Kg/Ha
        // Lote más productivo = LOT-01 - Lote Colina con 3,000 Kg/Ha
        $response = $this->actingAs($this->admin)->get(route('reportes.index', [
            'tipo_reporte' => 'productividad_lotes',
        ]));

        $response->assertOk();
        $response->assertSee('Rendimiento Promedio');
        $response->assertSee('1,666.67');
        $response->assertSee('Total Cosechado');
        $response->assertSee('10,000.0');
        $response->assertSee('Lote Más Productivo');
        $response->assertSee('LOT-01 - Lote Colina');
        $response->assertSee('3,000.00 Kg/Ha');
        $response->assertSee('Lotes Evaluados');
    }

    /**
     * Hardening DP-20: Exportación de datos de productividad a archivo CSV.
     */
    public function test_exportacion_de_reportes_a_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.index', [
            'tipo_reporte' => 'productividad_lotes',
            'export'       => 'csv',
        ]));

        $response->assertOk();
        $this->assertTrue(str_contains((string)$response->headers->get('content-disposition'), 'Reporte_Productividad_Lotes_SIGVOS.csv'));
    }

    /**
     * Exportación de datos de productividad a archivo Excel (.xlsx) corporativo con logo y formato.
     */
    public function test_exportacion_de_reportes_a_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.index', [
            'tipo_reporte' => 'productividad_lotes',
            'export'       => 'excel',
        ]));

        $response->assertOk();
        $this->assertTrue(str_contains((string)$response->headers->get('content-disposition'), 'Reporte_Productividad_Lotes_SIGVOS.xlsx'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }
}
