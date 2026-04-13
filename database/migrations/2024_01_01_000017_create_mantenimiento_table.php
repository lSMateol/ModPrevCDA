<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimiento', function (Blueprint $table) {
            $table->increments('idmant');
            $table->unsignedBigInteger('idveh');
            $table->unsignedBigInteger('idemp');
            $table->longText('descrip');
            $table->timestamp('fechareg')->useCurrent()->useCurrentOnUpdate();
            $table->date('fechamant');
            $table->date('fecnot')->nullable();
            $table->string('rutafact', 200);
            $table->unsignedBigInteger('valormant')->nullable();

            $table->foreign('idveh')->references('idveh')->on('vehiculo');
            $table->foreign('idemp')->references('idemp')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento');
    }
};
