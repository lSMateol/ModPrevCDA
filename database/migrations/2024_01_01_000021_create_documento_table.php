<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documento', function (Blueprint $table) {
            $table->id('iddoc'); //codigo del documento
            $table->unsignedBigInteger('idveh')->nullable();   // vehículo asociado
            $table->unsignedBigInteger('iddia')->nullable();   // diagnóstico asociado (opcional)
            $table->unsignedBigInteger('idper')->nullable();   // quién subió el archivo
            $table->string('nomdoc', 255);                     // nombre del archivo
            $table->string('rutadoc', 255);                    // ruta física del archivo
            $table->string('tipodoc', 10);                     // pdf, jpg, png
            $table->decimal('tamdoc', 8, 2)->nullable();       // tamaño en MB
            $table->string('estadoc', 20)->default('Pendiente'); // Verificado, Pendiente
            $table->timestamps();

            $table->foreign('idveh')->references('idveh')->on('vehiculo');
            $table->foreign('iddia')->references('iddia')->on('diag');
            $table->foreign('idper')->references('idper')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento');
    }
};
