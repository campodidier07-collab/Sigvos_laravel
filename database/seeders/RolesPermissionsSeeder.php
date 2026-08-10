<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────────────────────
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema',                         'activo' => true, 'creado_en' => now()],
            ['id' => 2, 'nombre' => 'Trabajador',    'descripcion' => 'Acceso operativo a sus lotes y actividades',      'activo' => true, 'creado_en' => now()],
        ]);

        // ── Permisos ───────────────────────────────────────────────────────────
        $permisos = [
            [1,  'lotes.crear',          'Crear nuevos lotes'],
            [2,  'lotes.editar',         'Editar lotes existentes'],
            [3,  'lotes.eliminar',       'Eliminar lotes'],
            [4,  'cultivos.crear',       'Registrar nuevos cultivos'],
            [5,  'cultivos.editar',      'Editar cultivos existentes'],
            [6,  'cultivos.eliminar',    'Eliminar cultivos'],
            [7,  'actividades.crear',    'Crear actividades'],
            [8,  'actividades.editar',   'Editar actividades'],
            [9,  'actividades.eliminar', 'Eliminar actividades'],
            [10, 'reportes.ver',         'Ver y generar reportes'],
            [11, 'usuarios.gestionar',   'Crear y editar usuarios'],
            [12, 'fotos.eliminar',       'Eliminar fotografías'],
        ];

        DB::table('permisos')->insert(
            array_map(fn ($p) => [
                'id'          => $p[0],
                'codigo'      => $p[1],
                'descripcion' => $p[2],
                'creado_en'   => now(),
            ], $permisos)
        );

        // ── Roles ↔ Permisos ───────────────────────────────────────────────────
        // Administrador: todos los permisos (1–12)
        $permisosAdmin = array_map(fn ($i) => ['id_rol' => 1, 'id_permiso' => $i], range(1, 12));
        // Trabajador: solo ver reportes y eliminar fotos
        $permisosTrabajador = [
            ['id_rol' => 2, 'id_permiso' => 10],
            ['id_rol' => 2, 'id_permiso' => 12],
        ];

        DB::table('roles_permisos')->insert(array_merge($permisosAdmin, $permisosTrabajador));
    }
}
