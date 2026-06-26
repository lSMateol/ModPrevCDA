<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna secret_answer a la tabla users para el
     * mecanismo de recuperación de contraseña por pregunta secreta.
     * La respuesta se almacena hasheada (bcrypt), nunca en texto plano.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('secret_answer')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('secret_answer');
        });
    }
};
