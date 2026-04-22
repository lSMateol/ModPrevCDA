<?php

use App\Support\LicenciaConduccion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE persona MODIFY catcon VARCHAR(5) NULL');

        $allowed = "'" . implode("','", LicenciaConduccion::CATEGORIAS) . "'";
        DB::statement("UPDATE persona SET catcon = NULL WHERE catcon IS NOT NULL AND catcon NOT IN ({$allowed})");
    }

    public function down(): void
    {
        DB::statement('UPDATE persona SET catcon = NULL WHERE catcon IS NOT NULL');
        DB::statement('ALTER TABLE persona MODIFY catcon BIGINT UNSIGNED NULL');
    }
};
