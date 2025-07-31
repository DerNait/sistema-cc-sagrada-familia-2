<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('nombre');
            $table->string('icono')->nullable()->after('imagen');
            $table->string('color')->nullable()->after('icono');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['imagen', 'icono', 'color']);
        });
    }
};
