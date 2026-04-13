<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tippar', function (Blueprint $table) {
            $table->id('idtip');
            $table->string('nomtip', 70)->nullable();
            $table->string('tittip', 150)->nullable();
            $table->unsignedBigInteger('idpef')->nullable();
            $table->tinyInteger('acttip')->default(1);
            $table->string('icotip', 250)->nullable();

            $table->foreign('idpef')->references('idpef')->on('perfil');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tippar');
    }
};
