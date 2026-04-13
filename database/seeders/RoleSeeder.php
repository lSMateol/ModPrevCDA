<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Role::firstOrCreate(['name' => 'Administrador']);
       Role::firstOrCreate(['name' => 'Digitador']);
       Role::firstOrCreate(['name' => 'Empresa']);
    }
}
