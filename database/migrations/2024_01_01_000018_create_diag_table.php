<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diag', function (Blueprint $table) {
            $table->id('iddia'); //codigo del diagnostico
            $table->dateTime('fecdia'); //fecha del diagnostico
            $table->foreignId('idveh')->constrained('vehiculo', 'idveh'); //codigo del vehiculo
            $table->tinyInteger('aprobado')->nullable(); // aprobado o no aprobado
            $table->foreignId('idper')->constrained('persona', 'idper'); //codigo del personal CDA
            $table->dateTime('fecvig')->nullable(); //fecha de vigencia del diagnostico
            $table->unsignedBigInteger('kilomt')->nullable(); //kilometraje del vehiculo al momento del diagnostico
            $table->foreignId('idinsp')->constrained('persona', 'idper'); //codigo de la inspeccion
            $table->foreignId('iding')->constrained('persona', 'idper'); //codigo del ingeniero
            // Creacion de llave diapar sin restricción
            $table->unsignedBigInteger('iddiapar')->nullable(); //codigo del detalle del diagnostico
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diag');
    }
};
