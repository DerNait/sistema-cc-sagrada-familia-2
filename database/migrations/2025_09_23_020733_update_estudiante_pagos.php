<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiante_pagos', function (Blueprint $table) {
           
            $table->unsignedBigInteger('aprobado_id')->nullable()->after('tipo_estado_id');
            $table->foreign('aprobado_id')->references('id')->on('empleados')->onDelete('cascade');

          
            $table->dateTime('aprobado_en')->nullable()->after('aprobado_id');
        });
    }

    public function down(): void
    {
        Schema::table('estudiante_pagos', function (Blueprint $table) {
         
            $table->dropForeign(['aprobado_id']);

           
            $table->dropColumn(['aprobado_id', 'aprobado_en']);
        });
    }
};
