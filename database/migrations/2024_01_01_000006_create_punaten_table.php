<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('punaten', function (Blueprint $table) {
            $table->id('idpun');
            $table->string('nompa', 150)->nullable();
            $table->string('nitpa', 12)->nullable();
            $table->string('dirpa', 150)->nullable();
            $table->string('telpa', 12)->nullable();
            $table->string('encarpa', 100)->nullable();
            $table->string('firmapa', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('punaten');
    }
};
