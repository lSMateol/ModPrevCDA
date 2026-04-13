<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id('idemp');
            $table->string('nonitem', 20)->nullable();
            $table->string('razsoem', 150)->nullable();
            $table->string('abremp', 20)->nullable();
            $table->string('direm', 150)->nullable();
            $table->string('telem', 10)->nullable();
            $table->string('emaem', 150)->nullable();
            $table->string('nomger', 70)->nullable();
            $table->string('usuaemp', 60)->nullable();   // ← Usuario NUEVO
            $table->string('passemp', 255)->nullable();  // ← Contraseña NUEVO
            $table->string('codcons', 50)->nullable();
            $table->unsignedBigInteger('codubi')->nullable();

            $table->foreign('codubi')->references('codubi')->on('ubica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
