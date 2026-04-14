<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valor', function (Blueprint $table) {
            $table->id('idval'); //codigo del valor
            $table->unsignedBigInteger('iddom')->nullable(); //codigo del dominio
            $table->string('nomval', 100)->nullable(); //nombre del valor
            $table->string('parval', 100)->nullable(); //parametro del valor
            $table->tinyInteger('actval')->nullable(); //estado del valor

            $table->foreign('iddom')->references('iddom')->on('dominio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor');
    }
};
