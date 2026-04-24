<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            if (!Schema::hasColumn('empresa', 'ciudeem')) {
                $table->string('ciudeem', 50)->nullable()->after('direm');
            }
            // Aumentar longitud de telem de 10 a 20 para coincidir con la validación del controlador
            $table->string('telem', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            if (Schema::hasColumn('empresa', 'ciudeem')) {
                $table->dropColumn('ciudeem');
            }
            $table->string('telem', 10)->nullable()->change();
        });
    }
};
