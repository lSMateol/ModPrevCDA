<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagina', function (Blueprint $table) {
            $table->id('idpag');
            $table->string('nompag', 255)->nullable();
            $table->string('rutpag', 255)->nullable();
            $table->tinyInteger('mospag')->nullable();
            $table->integer('ordpag')->nullable();
            $table->string('icopag', 255)->nullable();
            $table->text('despag')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagina');
    }
};
