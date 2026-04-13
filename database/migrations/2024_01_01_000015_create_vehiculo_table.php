<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculo', function (Blueprint $table) {
            $table->id('idveh');
            $table->string('nordveh', 30)->nullable();
            $table->integer('tipoveh');
            $table->string('placaveh', 6)->unique();
            $table->unsignedBigInteger('linveh');
            $table->integer('modveh');
            $table->string('paiveh', 150)->default('COLOMBIA');
            $table->date('fmatv')->nullable();
            $table->unsignedBigInteger('idemp')->nullable();
            $table->integer('capveh')->nullable();
            $table->unsignedBigInteger('clveh');
            $table->unsignedBigInteger('crgveh')->default(91)->nullable();
            $table->unsignedBigInteger('combuveh');
            $table->integer('cilveh')->nullable();
            $table->string('lictraveh', 15)->nullable();
            $table->string('colveh', 30)->nullable();
            $table->string('nmotveh', 30)->nullable();
            $table->unsignedBigInteger('tmotveh')->default(101)->nullable();
            $table->string('nchaveh', 30)->nullable();
            $table->string('taroperveh', 15)->nullable();
            $table->string('radaccveh', 255)->nullable();
            $table->date('fecexpr')->nullable();
            $table->date('fecvenr')->nullable();
            $table->string('soat', 15)->nullable();
            $table->date('fecvens')->nullable();
            $table->string('extcontveh', 15)->nullable();
            $table->date('fecvene')->nullable();
            $table->string('cactveh', 15)->nullable();
            $table->date('fecvenc')->nullable();
            $table->string('tecmecveh', 15)->nullable();
            $table->date('fecvent')->nullable();
            $table->tinyInteger('polaveh')->default(1);
            $table->tinyInteger('blinveh')->default(2);
            $table->unsignedBigInteger('prop')->nullable();
            $table->unsignedBigInteger('cond')->nullable();

            $table->foreign('clveh')->references('idval')->on('valor');
            $table->foreign('idemp')->references('idemp')->on('empresa');
            $table->foreign('linveh')->references('idmar')->on('marca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo');
    }
};
