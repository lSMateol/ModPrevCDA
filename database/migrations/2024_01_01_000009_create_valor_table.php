<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valor', function (Blueprint $table) {
            $table->id('idval'); // Código del valor
            $table->unsignedBigInteger('iddom'); // Quitamos el nullable, un valor siempre debe pertenecer a un dominio
            $table->string('nomval', 255)->nullable(); // Aumenté a 255 por estándar
            $table->string('parval', 100)->nullable(); // Parámetro del valor (ej: GAS, DIE)
            $table->tinyInteger('actval')->default(1); // Por defecto activo (1)

            // Relación con Dominio con borrado en cascada
            $table->foreign('iddom')
                  ->references('iddom')
                  ->on('dominio')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor');
    }
};
