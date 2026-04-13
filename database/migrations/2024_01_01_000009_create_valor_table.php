<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valor', function (Blueprint $table) {
            $table->id('idval');
            $table->unsignedBigInteger('iddom')->nullable();
            $table->string('nomval', 100)->nullable();
            $table->string('parval', 100)->nullable();
            $table->tinyInteger('actval')->nullable();

            $table->foreign('iddom')->references('iddom')->on('dominio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor');
    }
};
