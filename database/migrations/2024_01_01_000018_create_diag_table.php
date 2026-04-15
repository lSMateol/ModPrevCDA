<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diag', function (Blueprint $table) {
            $table->id('iddia'); //codigo del diagnostico
            $table->dateTime('fecdia')->nullable(); //fecha del diagnostico
            $table->unsignedBigInteger('idveh')->nullable(); //codigo del vehiculo
            $table->tinyInteger('aprobado')->nullable(); // aprobado o no aprobado
            $table->unsignedBigInteger('idper')->nullable(); //codigo del personal
            $table->dateTime('fecvig')->nullable(); //fecha de vigencia del diagnostico
            $table->unsignedBigInteger('kilomt')->nullable(); //kilometraje del vehiculo al momento del diagnostico
            $table->unsignedBigInteger('idinsp')->nullable(); //codigo de la inspeccion
            $table->unsignedBigInteger('iding')->nullable(); //codigo del ingeniero
            $table->unsignedBigInteger('dpiddia')->nullable(); //codigo del detalle del diagnostico
            $table->foreign('idveh')->references('idveh')->on('vehiculo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diag');
    }
};
