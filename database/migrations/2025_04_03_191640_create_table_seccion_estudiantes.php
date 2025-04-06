<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seccion_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seccion_id')->nullable(false);
            $table->foreign('seccion_id')->references('id')->on('secciones')->onDelete('cascade');
            $table->unsignedBigInteger('estudiante_id')->nullable(false);
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seccion_estudiantes');
    }
};