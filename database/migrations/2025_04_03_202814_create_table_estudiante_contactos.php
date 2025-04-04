<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estudiante_contactos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id')->nullable(false);
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->string('nombre',100)->nullable(false);
            $table->string('contacto',255)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiante_contactos');
    }
};