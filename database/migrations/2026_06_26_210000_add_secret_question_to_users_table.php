<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna secret_question a la tabla users.
     * Almacena la clave de la pregunta elegida por el usuario (ej: 'q1', 'q2').
     * La respuesta ya existe en la columna secret_answer (añadida en migración previa).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('secret_question', 10)->nullable()->after('secret_answer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('secret_question');
        });
    }
};
