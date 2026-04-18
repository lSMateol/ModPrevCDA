<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        // Leer y ejecutar directamente el archivo SQL nativo proporcionado
        $sqlFilePath = database_path('data/marca.sql');
        $sqlFilePath = database_path('data/marca.sql');
        if (file_exists($sqlFilePath)) {
            $content = file_get_contents($sqlFilePath);
            // Filtramos todo el DDL (CREATE TABLE, ALTER) usando regex para asegurar que solo se ejecute la data pura
            if (preg_match('/INSERT INTO `marca` \(`idmar`, `nommarlin`, `depmar`\) VALUES[\s\S]*?;/', $content, $matches)) {
                $insertStatements = $matches[0];
                \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
                DB::table('marca')->truncate();
                \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
                DB::unprepared($insertStatements);
            }
        }
    }
}
