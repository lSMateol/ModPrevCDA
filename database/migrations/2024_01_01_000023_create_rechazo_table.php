<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rechazo', function (Blueprint $table) {
            $table->id('idrec'); //codigo del rechazo
            $table->unsignedBigInteger('iddia');               // diagnóstico rechazado
            $table->unsignedBigInteger('idper_ant');           // inspector anterior
            $table->unsignedBigInteger('idper_nvo')->nullable(); // nuevo inspector asignado
            $table->text('motivo');                            // motivo principal del rechazo
            $table->string('prioridad', 20)->default('Alta'); // Alta, Media, Baja
            $table->text('campos_mod')->nullable();            // campos a modificar
            $table->text('notas')->nullable();                 // notas para el nuevo inspector
            $table->dateTime('fecreasig')->nullable();         // fecha nueva revisión
            $table->string('estadorec', 20)->default('Rechazado'); // Rechazado, Reasignado, Resuelto
            $table->timestamps();

            $table->foreign('iddia')->references('iddia')->on('diag');
            $table->foreign('idper_ant')->references('idper')->on('persona');
            $table->foreign('idper_nvo')->references('idper')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rechazo');
    }
};
