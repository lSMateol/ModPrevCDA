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
        if (file_exists($sqlFilePath)) {
            $content = file_get_contents($sqlFilePath);
            // Extraemos solo el bloque de VALUES
            if (preg_match('/INSERT INTO `marca` \(`idmar`, `nommarlin`, `depmar`\) VALUES\s*([\s\S]*?);/', $content, $matches)) {
                $valuesBlock = $matches[1];
                
                // Regex para capturar cada fila (id, 'nombre', dep)
                // Nota: maneja escapes simples de comillas si los hay, aunque en este SQL parecen ser directos
                preg_match_all('/\(\s*(\d+)\s*,\s*\'(.*?)\'\s*,\s*(\d+)\s*\)/', $valuesBlock, $rows, PREG_SET_ORDER);

                foreach ($rows as $row) {
                    DB::table('marca')->updateOrInsert(
                        ['idmar' => $row[1]],
                        [
                            'nommarlin' => $row[2],
                            'depmar'    => $row[3]
                        ]
                    );
                }
            }
        }
    }
}
