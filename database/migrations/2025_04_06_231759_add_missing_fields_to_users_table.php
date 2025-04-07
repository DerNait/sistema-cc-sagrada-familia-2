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
        DB::unprepared("
            INSERT INTO roles (id, nombre, created_at, updated_at)
            SELECT 5, 'Estudiante', NOW(), NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM roles WHERE id = 5
            )
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id')->default(5)->after('id');
            $table->string('apellido')->after('name')->nullable();
            $table->date('fecha_registro')->nullable()->after('email_verified_at');
            $table->date('fecha_nacimiento')->nullable()->after('fecha_registro');

            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropColumn(['rol_id', 'apellido', 'fecha_registro', 'fecha_nacimiento']);
        });
    }
};
