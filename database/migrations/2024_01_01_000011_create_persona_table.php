<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id('idper');
            $table->unsignedBigInteger('ndocper')->nullable()->unique();
            $table->unsignedBigInteger('tdocper');
            $table->string('nomper', 50);
            $table->string('apeper', 50);
            $table->string('dirper', 150)->nullable();
            $table->string('telper', 10);
            $table->unsignedBigInteger('codubi');
            $table->unsignedBigInteger('idpef');
            $table->string('pass', 50)->nullable();
            $table->string('emaper', 60);
            $table->unsignedBigInteger('idemp')->nullable();
            $table->string('nliccon', 20)->nullable();
            $table->date('fvencon')->nullable();
            $table->unsignedBigInteger('catcon')->nullable();
            $table->tinyInteger('actper');

            $table->foreign('idpef')->references('idpef')->on('perfil');
            $table->foreign('codubi')->references('codubi')->on('ubica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
