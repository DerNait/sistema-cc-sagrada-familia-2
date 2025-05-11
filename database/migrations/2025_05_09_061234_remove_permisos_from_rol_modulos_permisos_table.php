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
        Schema::table('rol_modulos_permisos', function (Blueprint $table) {
            $table->dropColumn('permiso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rol_modulos_permisos', function (Blueprint $table) {
            $table->string('permiso', 100)->nullable(false);
        });
    }
};
