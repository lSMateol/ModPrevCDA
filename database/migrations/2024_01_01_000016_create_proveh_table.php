<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proveh', function (Blueprint $table) {
            $table->unsignedBigInteger('idveh')->nullable(); //codigo de vehiculo
            $table->unsignedBigInteger('idper')->nullable(); //codigo de persona (propietario o conductor del vehículo)

            $table->foreign('idper')->references('idper')->on('persona');
            $table->foreign('idveh')->references('idveh')->on('vehiculo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveh');
    }
};
