<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('diag', 'tipo_formulario')) {
            Schema::table('diag', function (Blueprint $table) {
                $table->string('tipo_formulario')->nullable()->after('idval_combu');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diag', function (Blueprint $table) {
            //
        });
    }
};
