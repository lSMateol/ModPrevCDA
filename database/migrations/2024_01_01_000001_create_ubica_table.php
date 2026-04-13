<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ubica', function (Blueprint $table) {
            $table->id('codubi');
            $table->string('nomubi', 255)->nullable();
            $table->unsignedBigInteger('depubi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubica');
    }
};
