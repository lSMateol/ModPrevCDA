<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foto', function (Blueprint $table) {
            $table->id('idfot');
            $table->unsignedBigInteger('iddia')->nullable();
            $table->string('rutafoto', 255)->nullable();

            $table->foreign('iddia')->references('iddia')->on('diag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto');
    }
};
