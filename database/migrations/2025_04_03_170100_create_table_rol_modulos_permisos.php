<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rol_modulos_permisos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id')->nullable(false);
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unsignedBigInteger('modulo_permiso_id')->nullable(false);
            $table->foreign('modulo_permiso_id')->references('id')->on('modulos_permisos')->onDelete('cascade');
            $table->string('permiso',100)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rol_modulos_permisos');
    }
};