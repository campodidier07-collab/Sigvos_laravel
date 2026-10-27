<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\Usuario;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionesSincronizacionTrabajadorTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $trabajador;
    protected Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->admin = Usuario::factory()->create([
            'id_rol' => 1, // Admin
            'activo' => true,
        ]);

        $this->trabajador = Usuario::factory()->create([
            'nombre' => 'Camilo Sampedro',
            'email'  => 'camilo.trabajador@sigvos.test',
            'id_rol' => 2, // Trabajador
            'activo' => true,
        ]);
    }

    /**
     * Criterio 1: Visualización de notificaciones prioritarias.
     * Cuando ingreso al centro de notificaciones, el sistema muestra
     * las notificaciones ordenadas por prioridad (alta -> media -> baja) y fecha.
     */
    public function test_criterio_1_visualizacion_de_notificaciones_prioritarias(): void
    {
        // Notificación baja creada hace 1 hora
        $notifBaja = Notificacion::create([
            'id_usuario'      => $this->trabajador->id,
            'tipo'            => 'actividad',
            'prioridad'       => 'baja',
            'titulo'          => 'Labor informativa de rutina',
            'mensaje'         => 'Revisión periódica de cercas',
            'leida'           => false,
            'creado_en'       => now()->subHour(),
        ]);

        // Notificación alta creada hace 3 horas
        $notifAlta = Notificacion::create([
            'id_usuario'      => $this->trabajador->id,
            'tipo'            => 'alerta',
            'prioridad'       => 'alta',
            'titulo'          => 'URGENTE: Detección de plaga',
            'mensaje'         => 'Se detectó roya en el lote asignado',
            'leida'           => false,
            'creado_en'       => now()->subHours(3),
        ]);

        // Notificación media creada hace 2 horas
        $notifMedia = Notificacion::create([
            'id_usuario'      => $this->trabajador->id,
            'tipo'            => 'cultivo',
            'prioridad'       => 'media',
            'titulo'          => 'Riego programado para hoy',
            'mensaje'         => 'Turno de riego asignado',
            'leida'           => false,
            'creado_en'       => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->trabajador)->get(route('notificaciones.index'));

        $response->assertStatus(200);
        $response->assertSee('Centro de Notificaciones');
        $response->assertSee('URGENTE: Detección de plaga');
        $response->assertSee('Riego programado para hoy');
        $response->assertSee('Labor informativa de rutina');

        // Verificar el orden en la colección devuelta a la vista
        $items = $response->viewData('notificaciones')->items();
        $this->assertCount(3, $items);
        $this->assertEquals($notifAlta->id, $items[0]->id, 'La primera notificación debe ser de prioridad alta');
        $this->assertEquals($notifMedia->id, $items[1]->id, 'La segunda notificación debe ser de prioridad media');
        $this->assertEquals($notifBaja->id, $items[2]->id, 'La tercera notificación debe ser de prioridad baja');
    }

    /**
     * Criterio 2: Marcar notificación como leída.
     * Cuando marco una notificación como leída, el sistema cambia su estado
     * y deja de mostrarla como pendiente.
     */
    public function test_criterio_2_marcar_notificacion_como_leida(): void
    {
        $notificacion = Notificacion::create([
            'id_usuario' => $this->trabajador->id,
            'tipo'       => 'actividad',
            'prioridad'  => 'media',
            'titulo'     => 'Revisión de cultivo asignada',
            'mensaje'    => 'Por favor completar la labor antes del mediodía',
            'leida'      => false,
            'creado_en'  => now(),
        ]);

        $this->assertFalse($notificacion->leida);

        $response = $this->actingAs($this->trabajador)
            ->post(route('notificaciones.leida', $notificacion));

        $response->assertRedirect();

        $notificacion->refresh();
        $this->assertTrue($notificacion->leida);

        // Verificar que en la vista aparezca visualmente marcada como leída
        $viewResponse = $this->actingAs($this->trabajador)->get(route('notificaciones.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Leída');
    }

    /**
     * Criterio 3: Ejecución de sincronización manual.
     * Cuando selecciono "Sincronizar ahora", el sistema inicia la sincronización
     * y muestra el progreso/actualización en tiempo real.
     */
    public function test_criterio_3_ejecucion_de_sincronizacion_manual(): void
    {
        // 1. Verificamos que la interfaz contenga el botón "Sincronizar ahora" y el progreso
        $responseView = $this->actingAs($this->trabajador)->get(route('dashboard'));
        $responseView->assertStatus(200);
        $responseView->assertSee('Sincronizar ahora');
        $responseView->assertSee('workerSyncStatus');
        $responseView->assertSee('fetchWorkerData()');

        // 2. Ejecutar la sincronización hacia el endpoint /dashboard/data
        $responseSync = $this->actingAs($this->trabajador)
            ->getJson(route('dashboard.data'));

        $responseSync->assertStatus(200);
        $responseSync->assertJsonStructure([
            'estadisticas',
            'actividadesProximas',
            'actividadesHoy',
            'cosechasProximas',
            'lotesAsignados',
            'notificaciones',
            'timestamp',
        ]);
    }

    /**
     * Criterio 4: Indicador de estado de sincronización.
     * Muestra si está sincronizado, sin conexión o con error.
     */
    public function test_criterio_4_indicador_de_estado_de_sincronizacion(): void
    {
        $response = $this->actingAs($this->trabajador)->get(route('dashboard'));

        $response->assertStatus(200);
        // Indicador de sincronización activo
        $response->assertSee('workerSyncStatusText');
        $response->assertSee('Sincronizado');
        // Indicador de modo offline
        $response->assertSee('offlineWorkerBanner');
        $response->assertSee('Modo Offline activado');
        $response->assertSee('Sin conexión');
        // Indicador de error y reintento
        $response->assertSee('syncWorkerErrorAlert');
    }

    /**
     * Criterio 5: Recuperación tras fallo de sincronización.
     * Informa el error de sincronización y permite reintentar el proceso.
     */
    public function test_criterio_5_recuperacion_tras_fallo_de_sincronizacion(): void
    {
        $response = $this->actingAs($this->trabajador)->get(route('dashboard'));

        $response->assertStatus(200);
        // Alerta que informa claramente el error
        $response->assertSee('Ocurrió un error durante la sincronización');
        // Botón explícito para reintentar la sincronización
        $response->assertSee('Reintentar sincronización');
        $response->assertSee('syncWorkerErrorMessage');
    }

    /**
     * Criterio 6: Sincronización al recuperar conexión.
     * Sincroniza los registros pendientes y actualiza su estado.
     */
    public function test_criterio_6_sincronizacion_al_recuperar_conexion(): void
    {
        $response = $this->actingAs($this->trabajador)->get(route('dashboard'));

        $response->assertStatus(200);
        // Evento que escucha la recuperación de conexión a internet (online)
        $response->assertSee("window.addEventListener('online'", false);
        $response->assertSee('fetchWorkerData()');
        $response->assertSee('Dispositivo en línea: Ejecutando sincronización de registros');
        // Mecanismo de persistencia en localStorage para datos fuera de línea
        $response->assertSee('sigvos_dashboard_worker_cache');
    }
}
