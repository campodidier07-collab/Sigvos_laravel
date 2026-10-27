<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\FotoCultivo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SincronizacionNubeAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Lote $lote;
    protected Cultivo $cultivo;
    protected TipoActividad $tipoActividad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1, // Admin
            'activo' => true,
        ]);

        $this->lote = Lote::create([
            'identificador'     => 'LOTE-SYNC-01',
            'nombre'            => 'Lote Principal Central',
            'ubicacion'         => 'Sector A',
            'area_ha'           => 6.50,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
        ]);

        $this->cultivo = Cultivo::create([
            'id_lote'              => $this->lote->id,
            'id_variedad'          => 1,
            'codigo'               => 'CUL-SYNC-101',
            'estado'               => 'sembrado',
            'fecha_siembra'        => now()->subDays(15),
            'fecha_cosecha_estimada'=> now()->addDays(60),
            'activo_en_lote'       => 1,
            'registrado_por'       => $this->admin->id,
        ]);

        $this->tipoActividad = TipoActividad::first();
    }

    /**
     * Criterio 1: Sincronización automática con conexión WiFi.
     * Cuando el dispositivo detecta conexión WiFi y hay datos pendientes,
     * el sistema inicia la sincronización automática con la nube.
     */
    public function test_criterio_1_sincronizacion_automatica_con_conexion_wifi(): void
    {
        $response = $this->actingAs($this->admin)
            ->withHeaders(['X-Connection-Type' => 'wifi'])
            ->getJson(route('dashboard.data', ['tipo_red' => 'wifi']));

        $response->assertStatus(200);
        $response->assertJson([
            'tipo_red'            => 'wifi',
            'fotos_sincronizadas' => true,
        ]);

        // Verificamos presencia del listener en el dashboard
        $viewResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Sincronización automática de datos con la nube iniciada');
        $viewResponse->assertSee('Sincronizado (WiFi)');
    }

    /**
     * Criterio 2: Indicador visual de estado de sincronización.
     * Muestra el estado de sincronización de forma clara.
     */
    public function test_criterio_2_indicador_visual_de_estado_de_sincronizacion(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('liveSyncStatus');
        $response->assertSee('syncStatusText');
        $response->assertSee('liveDot');
    }

    /**
     * Criterio 3: Sincronización manual forzada.
     * Cuando selecciono "Sincronizar ahora", el sistema inicia la sincronización
     * y muestra el progreso de inmediato.
     */
    public function test_criterio_3_sincronizacion_manual_forzada(): void
    {
        $responseView = $this->actingAs($this->admin)->get(route('dashboard'));
        $responseView->assertStatus(200);
        $responseView->assertSee('btnManualRefresh');
        $responseView->assertSee('title="Sincronizar ahora"', false);
        $responseView->assertSee('fetchDashboardData(true)', false);

        $responseApi = $this->actingAs($this->admin)->getJson(route('dashboard.data'));
        $responseApi->assertStatus(200);
        $responseApi->assertJsonStructure([
            'estadisticas',
            'timestamp',
            'tipo_red',
        ]);
    }

    /**
     * Criterio 4: Funcionamiento offline básico.
     * Permite consultar y registrar información disponible localmente sin conexión.
     */
    public function test_criterio_4_funcionamiento_offline_basico(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('offlineBanner');
        $response->assertSee('Modo fuera de línea activo:');
        $response->assertSee('Las funciones de consulta y registro local siguen disponibles en modo offline');
        $response->assertSee('sigvos_dashboard_admin_cache');
    }

    /**
     * Criterio 5: Sincronización automática al recuperar conexión.
     * Sincroniza automáticamente la información pendiente al volver a tener conexión.
     */
    public function test_criterio_5_sincronizacion_automatica_al_recuperar_conexion(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee("window.addEventListener('online'", false);
        $response->assertSee('fetchDashboardData()');
        $response->assertSee('Conexión reanudada: Sincronización automática de datos con la nube iniciada');
    }

    /**
     * Criterio 6: Resolución de conflictos de datos más recientes.
     * Cuando se detectan conflictos durante la sincronización,
     * el sistema conserva la versión más reciente y registra el cambio.
     */
    public function test_criterio_6_resolucion_de_conflictos_de_datos_mas_recientes(): void
    {
        $actividad = Actividad::create([
            'id_cultivo'        => $this->cultivo->id,
            'id_tipo_actividad' => $this->tipoActividad->id,
            'descripcion'       => 'Poda fitosanitaria lote central',
            'fecha_programada'  => now()->toDateString(),
            'estado'            => 'pendiente',
            'prioridad'         => 'media',
            'creado_por'        => $this->admin->id,
        ]);

        // Simular que el cliente offline tiene una actualización con timestamp más reciente
        $timestampFuturo = now()->addHour()->toIso8601String();

        $response = $this->actingAs($this->admin)->json('GET', route('dashboard.data'), [
            'cambios_locales' => [
                [
                    'modelo'        => 'actividad',
                    'id'            => $actividad->id,
                    'estado'        => 'completada',
                    'observaciones' => 'Completada en campo en modo offline',
                    'updated_at'    => $timestampFuturo,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'conflictos_resueltos' => 1,
        ]);

        $actividad->refresh();
        $this->assertEquals('completada', $actividad->estado);
        $this->assertEquals('Completada en campo en modo offline', $actividad->observaciones);
    }

    /**
     * Criterio 7: Alerta de sincronización pendiente prolongada.
     * Informa al usuario cuando existen datos pendientes sin sincronizar tras varios días.
     */
    public function test_criterio_7_alerta_de_sincronizacion_pendiente_prolongada(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('prolongedSyncAlert');
        $response->assertSee('Alerta de sincronización pendiente prolongada:');
        $response->assertSee('Han pasado varios días sin sincronizar con la nube');
        $response->assertSee('verificarSincronizacionProlongada()');
    }

    /**
     * Criterio 8: Sincronización selectiva de fotografías.
     * Restringe o pospone la sincronización de fotografías cuando se detecta conexión móvil o limitada.
     */
    public function test_criterio_8_sincronizacion_selectiva_de_fotografias(): void
    {
        // 1. Conexión Móvil/Limitada -> Pospone fotografías
        $responseMovil = $this->actingAs($this->admin)
            ->withHeaders(['X-Connection-Type' => 'movil'])
            ->getJson(route('dashboard.data', ['tipo_red' => 'movil']));

        $responseMovil->assertStatus(200);
        $responseMovil->assertJson([
            'tipo_red'            => 'movil',
            'fotos_sincronizadas' => false,
        ]);

        // 2. Conexión WiFi -> Habilita descarga y sincronización completa
        $responseWifi = $this->actingAs($this->admin)
            ->withHeaders(['X-Connection-Type' => 'wifi'])
            ->getJson(route('dashboard.data', ['tipo_red' => 'wifi']));

        $responseWifi->assertStatus(200);
        $responseWifi->assertJson([
            'tipo_red'            => 'wifi',
            'fotos_sincronizadas' => true,
        ]);
    }
}
