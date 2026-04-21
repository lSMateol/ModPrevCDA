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

         // 2. Seeders originales (Ubica, Dominio, Perfil, Pagina, Marca, Empresa, Persona, Tippar, Vehiculo, Diag)
        $this->call([
            UbicaSeeder::class,
            DominioSeeder::class,
            PerfilSeeder::class,
            PaginaSeeder::class,
            MarcaSeeder::class,
            EmpresaSeeder::class,
            PersonaSeeder::class, // Crea registros en tabla 'persona' (incluye admin con email admin@cda.com)
            TipparSeeder::class,
            VehiculoSeeder::class,
            DiagSeeder::class,
        ]);

        $idperAdmin = \Illuminate\Support\Facades\DB::table('persona')->where('ndocper', 10000001)->value('idper');
        $idperDigitador = \Illuminate\Support\Facades\DB::table('persona')->where('ndocper', 10000002)->value('idper');
        $idempEmpresa = \Illuminate\Support\Facades\DB::table('empresa')->where('nonitem', '900555666-4')->value('idemp');

        // 3. Crear usuario administrador en la tabla 'users' (para autenticación Laravel)
        //    Usamos el mismo email que en PersonaSeeder para mantener coherencia.
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@cda.com'],   // Coincide con el email del administrador en PersonaSeeder
            [
                'name'     => 'Administrador',
                'password' => Hash::make('admin123'), // Misma contraseña que en persona (pero hasheada con bcrypt)
                'idper'    => $idperAdmin, // Vinculado a 'Diego Agustin'
                'idemp'    => null,
            ]
        );

        // Asignar rol 'Administrador' (debe existir gracias a RoleSeeder)
        $adminUser->assignRole('Administrador');

        // ==========================================
        // 3. USUARIO DIGITADOR
        // ==========================================
        $digitador = User::firstOrCreate(
            ['email' => 'digitador@admin.com'],
            [
                'name' => 'Digitador',
                'password' => Hash::make('12345678'),
                'idper'    => $idperDigitador, // Vinculado a 'Carlos Ruiz'
                'idemp'    => null,
            ]
        );

        // Asignar rol Digitador
        $digitador->assignRole('Digitador');


        // ==========================================
        // 4. USUARIO EMPRESA
        // ==========================================
        $empresa = User::firstOrCreate(
            ['email' => 'empresa@admin.com'],
            [
                'name' => 'Empresa',
                'password' => Hash::make('12345678'),
                'idper'    => null, 
                'idemp'    => $idempEmpresa, // Transportes del Norte
            ]
        );

        // Asignar rol Empresa
        $empresa->assignRole('Empresa');
        
    }
}