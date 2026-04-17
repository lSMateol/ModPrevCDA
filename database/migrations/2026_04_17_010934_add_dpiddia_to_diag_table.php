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
        Schema::table('diag', function (Blueprint $table) {
            $table->unsignedBigInteger('dpiddia')->nullable()->after('iddiapar');
            $table->foreign('dpiddia')->references('iddia')->on('diag')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diag', function (Blueprint $table) {
            $table->dropForeign(['dpiddia']);
            $table->dropColumn('dpiddia');
        });
    }
};
