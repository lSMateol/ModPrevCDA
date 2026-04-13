<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diag', function (Blueprint $table) {
            $table->id('iddia');
            $table->dateTime('fecdia')->nullable();
            $table->unsignedBigInteger('idpun')->nullable();
            $table->unsignedBigInteger('idveh')->nullable();
            $table->tinyInteger('aprobado')->nullable();
            $table->unsignedBigInteger('idper')->nullable();
            $table->dateTime('fecvig')->nullable();
            $table->unsignedBigInteger('idmaq');
            $table->unsignedBigInteger('kilomt')->nullable();
            $table->unsignedBigInteger('idinsp')->nullable();
            $table->unsignedBigInteger('iding')->nullable();
            $table->unsignedBigInteger('dpiddia')->nullable();

            $table->foreign('idveh')->references('idveh')->on('vehiculo');
            $table->foreign('idpun')->references('idpun')->on('punaten');
            $table->foreign('idmaq')->references('idmaq')->on('maquina');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diag');
    }
};
