<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diapar', function (Blueprint $table) {
            $table->id('iddiapar'); //codigo del diagnostico (PK)
            $table->foreignId('iddia')->constrained('diag', 'iddia')->onDelete('cascade'); //codigo del parametro
            $table->foreignId('idpar')->constrained('param', 'idpar'); //codigo del parametro
            $table->foreignId('idper')->constrained('persona', 'idper'); //codigo de la persona
            $table->text('valor')->nullable(); //valor del parametro para el diagnostico
            $table->timestamps();
      
        });
        // AHORA, añadimos la llave foránea que faltaba en 'diag'
        Schema::table('diag', function (Blueprint $table) {
            $table->foreign('iddiapar')->references('iddiapar')->on('diapar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diapar');
    }
};
