<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ajustes_salariales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_empleado_id')->nullable(false);
            $table->foreign('pago_empleado_id')->references('id')->on('pagos_empleados')->onDelete('cascade');
            $table->unsignedBigInteger('tipo_ajuste_id');
            $table->foreign('tipo_ajuste_id')->references('id')->on('tipo_ajustes')->onDelete('cascade');
            $table->text('descripcion');
            $table->decimal('monto',10,2)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ajustes_salariales');
    }
};