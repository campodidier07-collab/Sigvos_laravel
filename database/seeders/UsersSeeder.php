<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Usuarios de prueba del sistema SIGVOS.
     *
     *   Admin:      sergio@correo.com  / sergio123
     *   Trabajador: didier@correo.com  / didier123
     */
    public function run(): void
    {
        DB::table('usuarios')->insert([
            [
                'id_rol'            => 1,
                'nombre'            => 'Sergio',
                'email'             => 'sergio@correo.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('sergio123'),
                'telefono'          => '0000000000',
                'foto_perfil'       => null,
                'activo'            => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta'   => null,
                'ultimo_acceso'     => null,
                'remember_token'    => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'id_rol'            => 2,
                'nombre'            => 'Didier',
                'email'             => 'didier@correo.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('didier123'),
                'telefono'          => '0000000000',
                'foto_perfil'       => null,
                'activo'            => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta'   => null,
                'ultimo_acceso'     => null,
                'remember_token'    => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
