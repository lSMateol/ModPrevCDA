<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('perfil', function (Blueprint $table) {
            $table->id('idpef');
            $table->string('nompef', 255)->nullable();
            $table->unsignedBigInteger('pagpri')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil');
    }
};
