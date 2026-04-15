<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diapar', function (Blueprint $table) {
            $table->unsignedBigInteger('iddia')->nullable(); //codigo del diagnostico
            $table->unsignedBigInteger('idpar')->nullable(); //codigo del parametro
            $table->unsignedBigInteger('idper')->nullable(); //codigo de la persona
            $table->text('valor')->nullable(); //valor del parametro para el diagnostico

            $table->foreign('iddia')->references('iddia')->on('diag');
            $table->foreign('idpar')->references('idpar')->on('param');
            $table->foreign('idper')->references('idper')->on('persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diapar');
    }
};
