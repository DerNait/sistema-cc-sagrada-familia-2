<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida']);
            $table->integer('cantidad')->nullable(false);
            $table->string('descripcion')->nullable();
            $table->timestamp('fecha')->useCurrent();
        });
    }

    public function down() {
        Schema::dropIfExists('movimientos');
    }
};