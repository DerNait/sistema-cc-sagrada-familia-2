<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_fotos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                  ->constrained('usuarios')
                  ->cascadeOnDelete();
            $table->string('ruta', 255);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_fotos');
    }
};
