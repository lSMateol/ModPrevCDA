<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('historial', function (Blueprint $table) {
            $table->id('idhis'); //codigo del historial
            $table->string('tabla_ref', 50);               // tabla a la que pertenece el registro (vehiculo, diag, etc)
            $table->unsignedBigInteger('id_ref');          // id del registro referenciado
            $table->string('accion', 100);                 // "Mantenimiento Registrado", "Actualización de Estado", etc.
            $table->text('descripcion');                   // descripción detallada del evento
            $table->unsignedBigInteger('idper')->nullable(); // usuario que generó el evento (null = sistema)
            $table->boolean('es_sistema')->default(false); // true si fue generado automáticamente
            $table->timestamps();

            $table->foreign('idper')->references('idper')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial');
    }
};
