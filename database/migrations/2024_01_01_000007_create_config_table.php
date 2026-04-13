<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('config', function (Blueprint $table) {
            $table->id('idcof');
            $table->string('nomcof', 150)->nullable();
            $table->string('nitcof', 12)->nullable();
            $table->string('dircof', 150)->nullable();
            $table->string('telcof', 12)->nullable();
            $table->string('logcof', 255)->nullable();
            $table->string('emacof', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
