<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehiculo', function (Blueprint $table) {
            // Tipo de servicio: 1 = Particular, 2 = Público
            // Público requiere empresa asociada obligatoriamente
            // Particular puede o no tener empresa
            $table->tinyInteger('tipo_servicio')->default(2)->after('tipoveh');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculo', function (Blueprint $table) {
            $table->dropColumn('tipo_servicio');
        });
    }
};
