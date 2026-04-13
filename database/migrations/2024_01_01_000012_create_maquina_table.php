<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maquina', function (Blueprint $table) {
            $table->id('idmaq');
            $table->unsignedBigInteger('idpun')->nullable();
            $table->string('ipmaq', 255)->nullable();

            $table->foreign('idpun')->references('idpun')->on('punaten');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maquina');
    }
};
