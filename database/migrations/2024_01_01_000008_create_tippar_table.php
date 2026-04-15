<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tippar', function (Blueprint $table) {
            $table->id('idtip'); //codigo del tipo de parametro
            $table->string('nomtip', 70)->nullable(); //nombre del tipo de parametro
            $table->string('tittip', 150)->nullable(); //titulo del tipo de parametro
            $table->unsignedBigInteger('idpef')->nullable(); //codigo del perfil al que pertenece el tipo de parametro
            $table->tinyInteger('acttip')->default(1); //estado del tipo de parametro (1: activo, 0: inactivo)
            $table->string('icotip', 250)->nullable(); //icono del tipo de parametro

            $table->foreign('idpef')->references('idpef')->on('perfil');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tippar');
    }
};
