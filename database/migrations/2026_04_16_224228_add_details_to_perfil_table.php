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
        Schema::table('perfil', function (Blueprint $table) {
            $table->string('tipo_pef', 50)->nullable()->after('nompef');
            $table->text('des_pef')->nullable()->after('tipo_pef');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfil', function (Blueprint $table) {
            $table->dropColumn(['tipo_pef', 'des_pef']);
        });
    }
};
