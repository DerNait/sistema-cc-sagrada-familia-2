<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estudiantes_notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seccion_estudiante_id')->nullable(false);
            $table->foreign('seccion_estudiante_id')->references('id')->on('seccion_estudiantes')->onDelete('cascade');
            $table->unsignedBigInteger('actividad_id')->nullable(false);
            $table->foreign('actividad_id')->references('id')->on('actividades')->onDelete('cascade');
            $table->decimal('nota', 5,2)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiantes_notas');
    }
};