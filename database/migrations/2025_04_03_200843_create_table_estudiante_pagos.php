<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estudiante_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grado_precio_id')->nullable(false);
            $table->foreign('grado_precio_id')->references('id')->on('grado_precio')->onDelete('cascade');
            $table->unsignedBigInteger('estudiante_id')->nullable(false);
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->unsignedBigInteger('tipo_pago_id')->nullable(false);
            $table->foreign('tipo_pago_id')->references('id')->on('tipo_pago')->onDelete('cascade');
            $table->decimal('monto_pagado',10,2)->nullable(false);
            $table->integer('meses_pagados')->nullable(false);
            $table->date('periodo_inicio')->nullable(false);
            $table->date('periodo_fin')->nullable(false);
            $table->unsignedBigInteger('tipo_estado_id')->nullable(false);
            $table->foreign('tipo_estado_id')->references('id')->on('tipo_estado')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiante_pago');
    }
};