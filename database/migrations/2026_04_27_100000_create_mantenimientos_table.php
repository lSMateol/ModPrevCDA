<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id('idmant'); // PK
            $table->foreignId('idveh')->constrained('vehiculo', 'idveh')->onDelete('cascade');
            $table->foreignId('idemp')->constrained('empresa', 'idemp')->onDelete('cascade');
            $table->longText('descrip');
            $table->date('fechamant');
            $table->date('fecnot')->nullable();
            $table->string('rutafact', 255)->nullable();
            $table->bigInteger('valormant')->nullable();
            $table->timestamps(); // maneja fechareg y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
