<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculo', function (Blueprint $table) {
            $table->id('idveh'); // codigo de vehiculo
            $table->string('nordveh', 30)->nullable(); // numero interno del vehiculo
            $table->integer('tipoveh'); // tipo de vehiculo (1: automovil, 2: camioneta, 3: camión, etc.)
            $table->string('placaveh', 6)->unique(); // placa del vehiculo
            $table->unsignedBigInteger('linveh'); // linea del vehiculo (referencia a la tabla marca)
            $table->integer('modveh'); // modelo del vehiculo (año de fabricación)
            $table->string('paiveh', 150)->default('COLOMBIA');  // país de fabricación del vehículo
            $table->date('fmatv')->nullable(); // fecha de matriculación del vehículo
            $table->unsignedBigInteger('idemp')->nullable(); // referencia a la empresa propietaria del vehículo
            $table->integer('capveh')->nullable(); // capacidad del vehículo 
            $table->unsignedBigInteger('clveh'); // clase del vehículo (referencia a la tabla valor)
            $table->unsignedBigInteger('crgveh')->default(91)->nullable(); // Categoria del vehículo (referencia a la tabla valor)
            $table->unsignedBigInteger('combuveh'); // tipo de combustible del vehículo (referencia a la tabla valor)
            $table->integer('cilveh')->nullable(); // cilindraje del vehículo
            $table->string('lictraveh', 15)->nullable(); // licencia de tránsito del vehículo
            $table->string('colveh', 30)->nullable(); // color del vehículo
            $table->string('nmotveh', 30)->nullable(); // número de motor del vehículo
            $table->unsignedBigInteger('tmotveh')->default(101)->nullable(); // tipo de motor del vehículo (referencia a la tabla valor)
            $table->string('nchaveh', 30)->nullable(); // número de chasis del vehículo
            $table->string('taroperveh', 15)->nullable(); // tarjeta de operación del vehículo
            $table->string('radaccveh', 255)->nullable(); // radio de acción/accesorios del vehículo
            $table->date('fecexpr')->nullable(); // fecha de expedición de la tarjeta de operación
            $table->date('fecvenr')->nullable();  // fecha de vencimiento de la tatjeta de operación
            $table->string('soat', 15)->nullable(); // número de póliza del SOAT del vehículo
            $table->date('fecvens')->nullable(); // fecha de vencimiento del SOAT del vehículo
            $table->string('extcontveh', 15)->nullable(); // numero de extintor del vehículo
            $table->date('fecvene')->nullable(); // fecha de vencimiento del extintor del vehículo
            $table->string('cactveh', 15)->nullable(); // No se que es
            $table->date('fecvenc')->nullable(); // No se que es
            $table->string('tecmecveh', 15)->nullable(); // Numero de revision tecnico mecanica del vehículo
            $table->date('fecvent')->nullable(); // fecha de vencimiento de la revisión técnico mecánica del vehículo
            $table->tinyInteger('polaveh')->default(1); // tipo de póliza del vehículo (1: todo riesgo, 2: terceros, etc.)
            $table->tinyInteger('blinveh')->default(2); // blindaje del vehículo (1: sí, 2: no)
            $table->unsignedBigInteger('prop')->nullable(); // referencia al propietario del vehículo (puede ser una persona natural o jurídica, dependiendo de la implementación)
            $table->unsignedBigInteger('cond')->nullable(); // referencia al conductor habitual del vehículo (puede ser una persona natural, dependiendo de la implementación)

            $table->foreign('clveh')->references('idval')->on('valor');
            $table->foreign('idemp')->references('idemp')->on('empresa');
            $table->foreign('linveh')->references('idmar')->on('marca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo');
    }
};
