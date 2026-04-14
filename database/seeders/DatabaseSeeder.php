<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. Ejecutar el seeder de roles
        // ==========================================
        $this->call(RoleSeeder::class);

        // ==========================================
        // 2. USUARIO ADMINISTRADOR
        // ==========================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'], // evita duplicados
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
            ]
        );

        // Asignar rol Administrador
        $admin->assignRole('Administrador');


        // ==========================================
        // 3. USUARIO DIGITADOR
        // ==========================================
        $digitador = User::firstOrCreate(
            ['email' => 'digitador@admin.com'],
            [
                'name' => 'Digitador',
                'password' => Hash::make('12345678'),
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
            ]
        );

        // Asignar rol Empresa
        $empresa->assignRole('Empresa');
    }
}