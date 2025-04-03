<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modulos_permisos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modulo_id')->nullable(false);
            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('cascade');
            $table->string('permiso',100)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('modulos_permisos');
    }
};