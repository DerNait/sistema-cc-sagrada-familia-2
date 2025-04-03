<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
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
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};