<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maestro_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maestro_id')->nullable(false);
            $table->foreign('maestro_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->unsignedBigInteger('curso_id')->nullable(false);
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
            $table->unsignedBigInteger('seccion_id')->nullable(false);
            $table->foreign('seccion_id')->references('id')->on('secciones')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maestro_cursos');
    }
};