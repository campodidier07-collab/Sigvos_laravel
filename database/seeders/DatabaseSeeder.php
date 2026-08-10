<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Orden de ejecución (respeta dependencias de FK):
     *  1. RolesPermissionsSeeder → crea roles y permisos
     *  2. UsersSeeder            → necesita los roles
     *  3. CatalogSeeder          → tipos cultivo, variedades, tipos actividad
     */
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            UsersSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
