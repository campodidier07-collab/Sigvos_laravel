<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Lote;
use App\Models\Cultivo;
use App\Models\Variedad;
use App\Models\FotoCultivo;
use Carbon\Carbon;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HistorialFotograficoCultivosTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $admin;
    protected Lote $lote;
    protected Cultivo $cultivo;

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

        $this->lote = Lote::create([
            'identificador'     => 'DP16-L1',
            'nombre'            => 'Lote Fotografía',
            'ubicacion'         => 'Sector Cafetero',
            'area_ha'           => 5.0,
            'id_tipo_preferido' => 1,
            'estado'            => 'ocupado',
            'activo'            => true,
            'es_alternativo'    => false,
        ]);

        $variedad = Variedad::first();
        $this->cultivo = Cultivo::create([
            'id_lote'                => $this->lote->id,
            'id_variedad'            => $variedad->id,
            'codigo'                 => 'CUL-DP16-001',
            'fecha_siembra'          => Carbon::now()->subMonths(4)->toDateString(),
            'fecha_cosecha_estimada' => Carbon::now()->addMonths(2)->toDateString(),
            'estado'                 => 'sembrado',
            'activo_en_lote'         => $this->lote->id,
            'registrado_por'         => $this->admin->id,
        ]);
    }

    /**
     * Criterio 1: Captura de fotografía durante recorrido.
     * Cuando capturo una fotografía desde la aplicación, el sistema almacena la imagen
     * y la asocia al cultivo correspondiente.
     */
    public function test_criterio_1_captura_de_fotografia_durante_recorrido(): void
    {
        $fotoFake = UploadedFile::fake()->image('recorrido_lote.jpg', 1024, 768);

        $response = $this->actingAs($this->admin)->post(route('fotos.store', $this->cultivo), [
            'foto'        => $fotoFake,
            'descripcion' => 'Revisión fitosanitaria del lote durante el recorrido matutino.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fotos_cultivo', [
            'id_cultivo'  => $this->cultivo->id,
            'id_usuario'  => $this->admin->id,
            'descripcion' => 'Revisión fitosanitaria del lote durante el recorrido matutino.',
        ]);

        $foto = FotoCultivo::where('id_cultivo', $this->cultivo->id)->first();
        $this->assertNotNull($foto);
        Storage::disk('public')->assertExists($foto->ruta);
    }

    /**
     * Criterio 2: Visualización del progreso fotográfico cronológico.
     * Cuando consulto el historial fotográfico del cultivo, el sistema muestra las fotos
     * registradas de forma cronológica (más recientes primero).
     */
    public function test_criterio_2_visualizacion_del_progreso_fotografico_cronologico(): void
    {
        // Foto 1: Hace 20 días
        $fotoAntigua = FotoCultivo::create([
            'id_cultivo'    => $this->cultivo->id,
            'id_usuario'    => $this->admin->id,
            'ruta'          => 'cultivos/foto_antigua.jpg',
            'descripcion'   => 'Fase de brotación inicial.',
            'fecha_captura' => Carbon::now()->subDays(20),
        ]);

        // Foto 2: Hace 2 días
        $fotoReciente = FotoCultivo::create([
            'id_cultivo'    => $this->cultivo->id,
            'id_usuario'    => $this->admin->id,
            'ruta'          => 'cultivos/foto_reciente.jpg',
            'descripcion'   => 'Fase de floración abundante.',
            'fecha_captura' => Carbon::now()->subDays(2),
        ]);

        $response = $this->actingAs($this->admin)->get(route('cultivos.show', $this->cultivo));

        $response->assertOk();
        $fotosOrdenadas = $this->cultivo->fresh()->fotos;

        $this->assertCount(2, $fotosOrdenadas);
        $this->assertEquals($fotoReciente->id, $fotosOrdenadas->first()->id);
        $this->assertEquals($fotoAntigua->id, $fotosOrdenadas->last()->id);

        $response->assertSee('Fase de floración abundante.');
        $response->assertSee('Fase de brotación inicial.');
    }

    /**
     * Criterio 3: Limitación de almacenamiento local.
     * Cuando el sistema detecta el límite de almacenamiento local, muestra una advertencia
     * y gestiona el espacio disponible.
     */
    public function test_criterio_3_limitacion_de_almacenamiento_local(): void
    {
        $response = $this->actingAs($this->admin)->get(route('cultivos.show', $this->cultivo));

        $response->assertOk();
        $response->assertSee('MAX_OFFLINE_PHOTOS');
        $response->assertSee('Atención de Almacenamiento Local: Se ha alcanzado el límite de seguridad');
        $response->assertSee('pendientesFotos.shift()');
    }

    /**
     * Criterio 4: Sincronización automática de fotos con la nube.
     * Cuando el dispositivo recupera conexión a internet, el sistema sincroniza
     * automáticamente las fotos almacenadas.
     */
    public function test_criterio_4_sincronizacion_automatica_de_fotos_con_la_nube(): void
    {
        $response = $this->actingAs($this->admin)->get(route('cultivos.show', $this->cultivo));

        $response->assertOk();
        $response->assertSee('Sincronización automática iniciada');
        $response->assertSee('Transmitiendo');
        $response->assertSee('localStorage.removeItem(OFFLINE_PHOTOS_KEY)');
    }

    /**
     * Criterio 5: Captura en modo offline.
     * Cuando tomo una fotografía sin conexión, el sistema guarda la imagen localmente
     * y la deja pendiente de sincronización.
     */
    public function test_criterio_5_captura_en_modo_offline(): void
    {
        $response = $this->actingAs($this->admin)->get(route('cultivos.show', $this->cultivo));

        $response->assertOk();
        $response->assertSee('sigvos_offline_evidencias_fotos');
        $response->assertSee('Modo Offline: Fotografía conservada localmente en la memoria del dispositivo');
    }

    /**
     * Criterio 6: Eliminación de fotografías.
     * Cuando selecciono "Eliminar foto", el sistema elimina la fotografía seleccionada,
     * borra el archivo físico y actualiza el registro con confirmación.
     */
    public function test_criterio_6_eliminacion_de_fotografias(): void
    {
        $fotoPath = 'cultivos/evidencia_a_borrar.jpg';
        Storage::disk('public')->put($fotoPath, 'test-binary-data');

        $foto = FotoCultivo::create([
            'id_cultivo'  => $this->cultivo->id,
            'id_usuario'  => $this->admin->id,
            'ruta'        => $fotoPath,
            'descripcion' => 'Foto descartada por mala iluminación.',
        ]);

        Storage::disk('public')->assertExists($fotoPath);

        $response = $this->actingAs($this->admin)->delete(route('fotos.destroy', $foto));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('fotos_cultivo', [
            'id' => $foto->id,
        ]);

        Storage::disk('public')->assertMissing($fotoPath);
    }

    /**
     * Criterio 7: Agregar descripción a fotografía.
     * Cuando ingreso una descripción a la fotografía, el sistema guarda la descripción
     * junto con la imagen.
     */
    public function test_criterio_7_agregar_descripcion_a_fotografia(): void
    {
        $fotoFake = UploadedFile::fake()->image('detalle_cultivo.jpg', 800, 600);
        $descripcionDetallada = 'Aparición de brotes vigorosos tras la última fertilización nitrogenada.';

        $response = $this->actingAs($this->admin)->post(route('fotos.store', $this->cultivo), [
            'foto'        => $fotoFake,
            'descripcion' => $descripcionDetallada,
        ]);

        $response->assertRedirect();

        $foto = FotoCultivo::where('id_cultivo', $this->cultivo->id)
            ->where('descripcion', $descripcionDetallada)
            ->first();

        $this->assertNotNull($foto);
        $this->assertEquals($descripcionDetallada, $foto->descripcion);
    }
}
