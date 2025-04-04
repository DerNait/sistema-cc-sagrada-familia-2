<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grado_precio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grado_id')->nullable(false);
            $table->foreign('grado_id')->references('id')->on('grado')->onDelete('cascade');
            $table->decimal('mensualidad',10,2)->nullable(false);
            $table->decimal('inscripcion',10,2)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grado_precio');
    }
};