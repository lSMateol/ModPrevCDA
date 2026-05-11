<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Agregar campo 'se_mantiene' a la tabla 'param'
        Schema::table('param', function (Blueprint $table) {
            $table->boolean('se_mantiene')->default(false)->after('actpar');
        });

        // 2. Crear tabla de configuración para tipos de vehículo (Combustible)
        Schema::create('tipo_vehiculo_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idval_combu'); // Relación con tabla 'valor' (dominio combustible)
            $table->unsignedBigInteger('idtip');      // Relación con tabla 'tippar' (Dominio/Sección)
            $table->unsignedBigInteger('idpar');      // Relación con tabla 'param' (Parámetro)
            $table->integer('orden')->default(0);

            $table->foreign('idval_combu')->references('idval')->on('valor');
            $table->foreign('idtip')->references('idtip')->on('tippar');
            $table->foreign('idpar')->references('idpar')->on('param');
        });

        // 3. Agregar campo 'idval_combu' a la tabla 'diag' para guardar qué tipo de vehículo se usó
        Schema::table('diag', function (Blueprint $table) {
            $table->unsignedBigInteger('idval_combu')->nullable()->after('idveh');
            $table->foreign('idval_combu')->references('idval')->on('valor');
        });
    }

    public function down(): void
    {
        Schema::table('diag', function (Blueprint $table) {
            $table->dropForeign(['idval_combu']);
            $table->dropColumn('idval_combu');
        });

        Schema::dropIfExists('tipo_vehiculo_config');

        Schema::table('param', function (Blueprint $table) {
            $table->dropColumn('se_mantiene');
        });
    }
};
