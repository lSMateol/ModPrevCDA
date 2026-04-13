<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('param', function (Blueprint $table) {
            $table->id('idpar');
            $table->string('nompar', 100)->nullable();
            $table->unsignedBigInteger('idtip')->nullable();
            $table->double('rini', 6, 2)->nullable();
            $table->double('rfin', 6, 2)->nullable();
            $table->string('control', 50)->nullable();
            $table->string('nomcampo', 30)->nullable();
            $table->string('unipar', 50)->nullable();
            $table->integer('colum')->nullable();
            $table->tinyInteger('actpar')->default(1);
            $table->integer('can');

            $table->foreign('idtip')->references('idtip')->on('tippar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('param');
    }
};
