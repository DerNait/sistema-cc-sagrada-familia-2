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
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('secciones', function (Blueprint $table) {
            $table->dropForeign(['mestro_guia_id']);
            $table->foreign('mestro_guia_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('maestro_cursos', function (Blueprint $table) {
            $table->dropForeign(['maestro_id']);
            $table->foreign('maestro_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('seccion_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropForeign(['vendedor_id']);
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendedor_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('estudiante_pagos', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('estudiante_contactos', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('pagos_empleados', function (Blueprint $table) {
            $table->dropForeign(['empleado_id']);
            $table->foreign('empleado_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::dropIfExists('usuarios');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id')->nullable(false);
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('restrict');
            $table->string('nombre')->nullable(false);
            $table->string('apellido', 100)->nullable(false);
            $table->string('email', 255)->unique()->nullable(false);
            $table->string('contra', 255)->nullable(false);
            $table->date('fecha_registro')->nullable(false);
            $table->date('fecha_nacimiento')->nullable(false);
            $table->timestamps();
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('secciones', function (Blueprint $table) {
            $table->dropForeign(['mestro_guia_id']);
            $table->foreign('mestro_guia_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('maestro_cursos', function (Blueprint $table) {
            $table->dropForeign(['maestro_id']);
            $table->foreign('maestro_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('seccion_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropForeign(['vendedor_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('vendedor_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('estudiante_pagos', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('estudiante_contactos', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    
        Schema::table('pagos_empleados', function (Blueprint $table) {
            $table->dropForeign(['empleado_id']);
            $table->foreign('empleado_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }
};
