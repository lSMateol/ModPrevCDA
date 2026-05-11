<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foto', function (Blueprint $table) {
            $table->id('idfot'); //codigo de la foto
            $table->unsignedBigInteger('iddia')->nullable(); //codigo del diagnostico
            $table->string('rutafoto', 255)->nullable(); //ruta de la foto

            $table->foreign('iddia')->references('iddia')->on('diag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto');
    }
};
