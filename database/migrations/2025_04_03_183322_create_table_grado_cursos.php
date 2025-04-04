<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grado_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grado_id')->nullable(false);
            $table->foreign('grado_id')->references('id')->on('grado')->onDelete('cascade');
            $table->unsignedBigInteger('curso_id')->nullable(false);
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grado_cursos');
    }
};