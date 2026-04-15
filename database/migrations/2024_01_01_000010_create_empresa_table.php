<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id('idemp'); // codigo de empresa
            $table->string('nonitem', 20)->nullable(); // numero de identificador tributario de la empresa
            $table->string('razsoem', 150)->nullable(); // razón social de la empresa
            $table->string('abremp', 20)->nullable(); // abreviatura de la empresa
            $table->string('direm', 150)->nullable(); // dirección de la empresa
            $table->string('telem', 10)->nullable(); // teléfono de la empresa
            $table->string('emaem', 150)->nullable(); // correo electrónico de la empresa
            $table->string('nomger', 70)->nullable(); // nombre del gerente de la empresa
            $table->string('usuaemp', 60)->nullable();   // Usuario empresa NUEVO
            $table->string('passemp', 255)->nullable();  // Contraseña empresa NUEVO
            $table->string('codcons', 50)->nullable(); // Codigo consecutivo de la empresa
            $table->unsignedBigInteger('idpef'); // id del perfil de la empresa
            $table->unsignedBigInteger('codubi')->nullable(); // codigo de ubicación de la empresa

            $table->foreign('idpef')->references('idpef')->on('perfil');
            $table->foreign('codubi')->references('codubi')->on('ubica');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
