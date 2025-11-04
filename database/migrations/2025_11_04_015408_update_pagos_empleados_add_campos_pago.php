<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_empleados', function (Blueprint $table) {
            // 1) Corregir FK de empleado_id: primero soltar y luego recrear hacia empleados.id
            $table->dropForeign(['empleado_id']);
            $table->foreign('empleado_id')
                ->references('id')->on('empleados')
                ->onDelete('cascade');

            // 2) Nuevos campos solicitados
            $table->date('fecha_ingreso')->nullable()->after('empleado_id');

            $table->decimal('salario_base', 10, 2)->nullable()->after('fecha_ingreso');
            $table->decimal('bonificacion_ley', 10, 2)->nullable()->default(0)->after('salario_base');
            $table->decimal('bonificacion_extra', 10, 2)->nullable()->default(0)->after('bonificacion_ley');
            $table->decimal('descuento_igss', 10, 2)->nullable()->default(0)->after('bonificacion_extra');
            $table->decimal('descuentos_varios', 10, 2)->nullable()->default(0)->after('descuento_igss');

            // Total (si lo vas a setear/calcular en app; si lo quieres generado en DB, mira la nota abajo)
            $table->decimal('total', 10, 2)->nullable()->after('descuentos_varios');

            // 3) Quitar columnas viejas si ya no se usarán
            if (Schema::hasColumn('pagos_empleados', 'monto_base')) {
                $table->dropColumn('monto_base');
            }
            if (Schema::hasColumn('pagos_empleados', 'monto_total')) {
                $table->dropColumn('monto_total');
            }
        });

        // (Opcional) Checks si tu motor los soporta (MySQL 8+)
        try {
            DB::statement('ALTER TABLE pagos_empleados ADD CONSTRAINT chk_mes CHECK (periodo_mes BETWEEN 1 AND 12)');
            DB::statement('ALTER TABLE pagos_empleados ADD CONSTRAINT chk_anio CHECK (periodo_anio BETWEEN 1900 AND 2100)');
        } catch (\Throwable $e) {
            // ignora si tu motor no soporta CHECK
        }
    }

    public function down(): void
    {
        Schema::table('pagos_empleados', function (Blueprint $table) {
            // Restaurar columnas antiguas
            if (!Schema::hasColumn('pagos_empleados', 'monto_base')) {
                $table->decimal('monto_base', 10, 2)->after('tipo_estado_id');
            }
            if (!Schema::hasColumn('pagos_empleados', 'monto_total')) {
                $table->decimal('monto_total', 10, 2)->after('monto_base');
            }

            // Quitar los campos nuevos
            $table->dropColumn([
                'total',
                'descuentos_varios',
                'descuento_igss',
                'bonificacion_extra',
                'bonificacion_ley',
                'salario_base',
                'fecha_ingreso',
            ]);

            // Revertir FK a usuarios (como estaba originalmente)
            $table->dropForeign(['empleado_id']);
            $table->foreign('empleado_id')
                ->references('id')->on('usuarios')
                ->onDelete('cascade');
        });

        // Quitar checks si se crearon
        try { DB::statement('ALTER TABLE pagos_empleados DROP CONSTRAINT chk_mes'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE pagos_empleados DROP CONSTRAINT chk_anio'); } catch (\Throwable $e) {}
    }
};
