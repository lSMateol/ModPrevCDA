<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('persona', 'ciuper')) {
            Schema::table('persona', function (Blueprint $table) {
                $table->string('ciuper', 50)->nullable()->after('dirper');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('persona', 'ciuper')) {
            Schema::table('persona', function (Blueprint $table) {
                $table->dropColumn('ciuper');
            });
        }
    }
};
