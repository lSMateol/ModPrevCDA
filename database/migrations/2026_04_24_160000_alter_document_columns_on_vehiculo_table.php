<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehiculo', function (Blueprint $table) {
            $table->string('lictraveh', 30)->nullable()->change();
            $table->string('taroperveh', 30)->nullable()->change();
            $table->string('soat', 30)->nullable()->change();
            $table->string('extcontveh', 30)->nullable()->change();
            $table->string('cactveh', 30)->nullable()->change();
            $table->string('tecmecveh', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehiculo', function (Blueprint $table) {
            $table->string('lictraveh', 15)->nullable()->change();
            $table->string('taroperveh', 15)->nullable()->change();
            $table->string('soat', 15)->nullable()->change();
            $table->string('extcontveh', 15)->nullable()->change();
            $table->string('cactveh', 15)->nullable()->change();
            $table->string('tecmecveh', 15)->nullable()->change();
        });
    }
};
