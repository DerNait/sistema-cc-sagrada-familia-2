<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiante_pagos', function (Blueprint $table) {
           
            $table->unsignedBigInteger('grado_precio_id')->after('id');
            $table->unsignedBigInteger('estudiante_id')->after('grado_precio_id');
            $table->unsignedBigInteger('tipo_pago_id')->after('estudiante_id');
            $table->decimal('monto_pagado', 10, 2)->after('tipo_pago_id');
            $table->integer('meses_pagados')->after('monto_pagado');
            $table->date('periodo_inicio')->after('meses_pagados');
            $table->date('periodo_fin')->after('periodo_inicio');
            $table->unsignedBigInteger('tipo_estado_id')->after('periodo_fin');

          
            $table->foreign('grado_precio_id')->references('id')->on('grado_precio')->onDelete('cascade');
            $table->foreign('estudiante_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('tipo_pago_id')->references('id')->on('tipo_pagos')->onDelete('cascade');
            $table->foreign('tipo_estado_id')->references('id')->on('tipo_estados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('estudiante_pagos', function (Blueprint $table) {
           
            $table->dropForeign(['grado_precio_id']);
            $table->dropForeign(['estudiante_id']);
            $table->dropForeign(['tipo_pago_id']);
            $table->dropForeign(['tipo_estado_id']);

            
            $table->dropColumn([
                'grado_precio_id',
                'estudiante_id',
                'tipo_pago_id',
                'monto_pagado',
                'meses_pagados',
                'periodo_inicio',
                'periodo_fin',
                'tipo_estado_id',
            ]);
        });
    }
};
