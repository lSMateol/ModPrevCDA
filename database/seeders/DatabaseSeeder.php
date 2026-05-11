<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // 1. Roles (necesario para permisos y páginas)
        $this->call(RoleSeeder::class);

         // 2. Seeders de Catálogos / Diccionarios
        $this->call([
            UbicaSeeder::class,
            DominioSeeder::class,
            PerfilSeeder::class,
            PaginaSeeder::class,
            MarcaSeeder::class,
            TipparSeeder::class,
        ]);

        /* 
         * IMPORTANTE: Los datos estáticos de Empresa, Persona, Vehiculo y Diag han sido eliminados.
         * Para poblar el sistema con datos reales aplicando la nueva lógica de negocio (Consolidación a Perfil 8, 
         * limpieza de Superadmin, y restricción de cuentas users), debe ejecutarse el:
         * 
         * $this->call(LegacyImportSeeder::class);
         * 
         * Asegúrate de tener la conexión 'legacy' configurada en database.php apuntando a cdarastr_cdarev
         * antes de descomentar la línea anterior.
         */
    }
}