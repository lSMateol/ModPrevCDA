<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UbicaSeeder::class,
            DominioSeeder::class,
            PerfilSeeder::class,
            PaginaSeeder::class,
            MarcaSeeder::class,
            EmpresaSeeder::class,
            PersonaSeeder::class,
            TipparSeeder::class,
            VehiculoSeeder::class,
            DiagSeeder::class,
        ]);
    }
}
