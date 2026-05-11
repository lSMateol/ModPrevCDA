<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagina', function (Blueprint $table) {
            $table->id('idpag'); //codigo de la pagina
            $table->string('nompag', 255)->nullable(); //nombre de la pagina
            $table->string('rutpag', 255)->nullable(); //ruta de la pagina
            $table->tinyInteger('mospag')->nullable(); //mostrar en el menu
            $table->integer('ordpag')->nullable(); //orden de la pagina
            $table->string('icopag', 255)->nullable(); //icono de la pagina
            $table->text('despag')->nullable(); //descripcion de la pagina
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagina');
    }
};
