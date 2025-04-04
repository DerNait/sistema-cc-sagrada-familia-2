<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bolsas_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bolsa_id')->nullable(false);
            $table->foreign('bolsa_id')->references('id')->on('bolsas')->onDelete('cascade');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bolsas_detalles');
    }
};