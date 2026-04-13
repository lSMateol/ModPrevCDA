<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagper', function (Blueprint $table) {
            $table->unsignedBigInteger('idpag')->nullable();
            $table->unsignedBigInteger('idpef')->nullable();

            $table->foreign('idpag')->references('idpag')->on('pagina');
            $table->foreign('idpef')->references('idpef')->on('perfil');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagper');
    }
};
