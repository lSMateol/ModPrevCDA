<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id('idper'); //codigo de la persona
            $table->unsignedBigInteger('tdocper'); //tipo de documento de la persona
            $table->unsignedBigInteger('ndocper')->unique(); //numero de documento de la persona
            $table->string('nomper', 50); //nombre de la persona
            $table->string('apeper', 50); //apellido de la persona
            $table->string('dirper', 150)->nullable(); //direccion de la persona
            $table->string('telper', 10); //telefono de la persona
            $table->unsignedBigInteger('codubi'); //codigo de la ubicacion de la persona
            $table->unsignedBigInteger('idpef')->nullable(); //codigo del perfil de la persona
            $table->string('pass', 50)->nullable(); //contraseña de la persona
            $table->string('emaper', 60); //email de la persona
            $table->unsignedBigInteger('idemp')->nullable(); //codigo de la empresa a la que pertenece la persona
            $table->string('nliccon', 20)->nullable(); //numero de licencia de conducir de la persona
            $table->date('fvencon')->nullable(); //fecha de vencimiento de la licencia de conducir de la persona
            $table->unsignedBigInteger('catcon')->nullable(); //categoria de la licencia de conducir de la persona
            $table->tinyInteger('actper'); //estado de la persona (1: activo, 0: inactivo)

            $table->foreign('idpef')->references('idpef')->on('perfil');
            $table->foreign('codubi')->references('codubi')->on('ubica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
