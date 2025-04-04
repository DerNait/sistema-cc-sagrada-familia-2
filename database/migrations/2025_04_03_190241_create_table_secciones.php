<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mestro_guia_id')->nullable(false);
            $table->foreign('mestro_guia_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->unsignedBigInteger('grado_id')->nullable(false);
            $table->foreign('grado_id')->references('id')->on('grado')->onDelete('cascade');
            $table->string('seccion',1)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('secciones');
    }
};