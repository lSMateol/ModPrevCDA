<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('param', function (Blueprint $table) {
            $table->id('idpar'); //codigo de parametro
            $table->string('nompar', 100); //nombre del parametro
            $table->unsignedBigInteger('idtip')->nullable(); //codigo de tipo de parametro
            $table->decimal('rini', 6, 2)->nullable(); //rango inicial
            $table->decimal('rfin', 6, 2)->nullable(); //rango final
            $table->string('control', 50); //tipo de control
            $table->string('nomcampo', 30); //nombre del campo
            $table->string('unipar', 50)->nullable(); //unidad de medida del parametro
            $table->integer('colum')->nullable(); //numero de columna en el formulario
            $table->tinyInteger('actpar')->default(1); //estado del parametro (1: activo, 0: inactivo)
            $table->integer('can'); //cantidad de parametros

            $table->foreign('idtip')->references('idtip')->on('tippar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('param');
    }
};
