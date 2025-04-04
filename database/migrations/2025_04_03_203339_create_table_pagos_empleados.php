<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pagos_empleados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empleado_id')->nullable(false);
            $table->foreign('empleado_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->integer('periodo_mes')->nullable(false);
            $table->integer('periodo_anio')->nullable(false);
            $table->unsignedBigInteger('tipo_estados');
            $table->foreign('tipo_estados')->references('id')->on('tipo_estados')->onDelete('cascade');
            $table->decimal('monto_base',10,2)->nullable(false);
            $table->decimal('monto_total',10,2)->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_empleados');
    }
};