<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50)->unique(); // Ej: Entrada, Salida, Ajuste
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_movimientos');
    }
};
