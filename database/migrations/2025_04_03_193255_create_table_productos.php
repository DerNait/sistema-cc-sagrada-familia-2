<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_producto_id')->nullable(false);
            $table->foreign('tipo_producto_id')->references('id')->on('tipo_productos')->onDelete('cascade');
            $table->string('nombre',100)->nullable(false);
            $table->date('fecha_ingreso')->nullable(false);
            $table->decimal('precio_unitario',10,2)->nullable(false);
            $table->integer('cantidad')->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};