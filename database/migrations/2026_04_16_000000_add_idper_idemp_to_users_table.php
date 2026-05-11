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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('idper')->nullable()->after('remember_token');
            $table->unsignedBigInteger('idemp')->nullable()->after('idper');
            
            $table->foreign('idper')->references('idper')->on('persona')->nullOnDelete();
            $table->foreign('idemp')->references('idemp')->on('empresa')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['idper']);
            $table->dropForeign(['idemp']);
            $table->dropColumn(['idper', 'idemp']);
        });
    }
};
