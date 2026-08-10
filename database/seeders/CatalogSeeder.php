<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tipos de Cultivo ───────────────────────────────────────────────────
        DB::table('tipos_cultivo')->insert([
            ['id' => 1, 'codigo' => 'CAFE',    'nombre' => 'Café',    'descripcion' => 'Cultivo de café en sus distintas variedades', 'activo' => true, 'creado_en' => now()],
            ['id' => 2, 'codigo' => 'MAIZ',    'nombre' => 'Maíz',    'descripcion' => 'Cultivo de maíz amarillo y dulce',            'activo' => true, 'creado_en' => now()],
            ['id' => 3, 'codigo' => 'PLATANO', 'nombre' => 'Plátano', 'descripcion' => 'Cultivo de plátano y banano',                 'activo' => true, 'creado_en' => now()],
            ['id' => 4, 'codigo' => 'YUCA',    'nombre' => 'Yuca',    'descripcion' => 'Cultivo de yuca blanca y amarilla',           'activo' => true, 'creado_en' => now()],
        ]);

        // ── Variedades ─────────────────────────────────────────────────────────
        $variedades = [
            // Café (id_tipo_cultivo = 1)
            [1, 'Arábica',          240, 300, 270],
            [1, 'Castillo',         240, 300, 270],
            [1, 'Colombia',         240, 300, 270],
            [1, 'Caturra',          240, 300, 270],
            [1, 'Tabi',             270, 330, 300],
            // Maíz (id_tipo_cultivo = 2)
            [2, 'Maíz Amarillo',    100, 130, 115],
            [2, 'Maíz Dulce',        80, 100,  90],
            [2, 'Maíz Blanco',      100, 130, 115],
            [2, 'Maíz Pira',         90, 120, 105],
            // Plátano (id_tipo_cultivo = 3)
            [3, 'Dominico Hartón',  270, 330, 300],
            [3, 'Cachaco',          270, 330, 300],
            [3, 'Banano Cavendish', 270, 300, 285],
            // Yuca (id_tipo_cultivo = 4)
            [4, 'Yuca Blanca',      240, 300, 270],
            [4, 'Yuca Amarilla',    270, 330, 300],
        ];

        DB::table('variedades')->insert(
            array_map(fn ($v) => [
                'id_tipo_cultivo'       => $v[0],
                'nombre'                => $v[1],
                'dias_cosecha_min'      => $v[2],
                'dias_cosecha_max'      => $v[3],
                'dias_cosecha_promedio' => $v[4],
                'activo'                => true,
                'creado_en'             => now(),
            ], $variedades)
        );

        // ── Tipos de Actividad ─────────────────────────────────────────────────
        $tiposActividad = [
            // [id, codigo, nombre, descripcion, dias_recordatorio, dias_frecuencia]
            [1, 'RIEGO',         'Riego',         'Aplicación de agua al cultivo',                    1,  7],
            [2, 'FERTILIZACION', 'Fertilización', 'Aplicación de fertilizantes o abonos',             2, 30],
            [3, 'PODA',          'Poda',          'Corte y mantenimiento de ramas',                   2, 60],
            [4, 'FUMIGACION',    'Fumigación',    'Control de plagas y enfermedades',                 1, 15],
            [5, 'COSECHA',       'Cosecha',       'Recolección del producto',                         3,  0],
            [6, 'SIEMBRA',       'Siembra',       'Plantación de semillas o plántulas',               1,  0],
            [7, 'DESHIERBE',     'Deshierbe',     'Eliminación de malezas',                           1, 21],
            [8, 'MONITOREO',     'Monitoreo',     'Revisión y seguimiento del estado del cultivo',    1,  7],
        ];

        DB::table('tipos_actividad')->insert(
            array_map(fn ($a) => [
                'id'                        => $a[0],
                'codigo'                    => $a[1],
                'nombre'                    => $a[2],
                'descripcion'               => $a[3],
                'dias_recordatorio_previo'  => $a[4],
                'dias_frecuencia_sugerida'  => $a[5] > 0 ? $a[5] : null,
                'activo'                    => true,
                'creado_en'                 => now(),
            ], $tiposActividad)
        );
    }
}
