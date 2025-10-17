<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            // 🔹 Si todavía no existe, agregamos el tipo de movimiento
            if (!Schema::hasColumn('movimientos', 'tipo_movimiento_id')) {
                $table->foreignId('tipo_movimiento_id')
                    ->nullable()
                    ->constrained('tipo_movimientos')
                    ->nullOnDelete()
                    ->after('id');
            }

            // 🔹 Campos de auditoría de stock
            if (!Schema::hasColumn('movimientos', 'stock_pre')) {
                $table->integer('stock_pre')->default(0)->after('cantidad');
            }

            if (!Schema::hasColumn('movimientos', 'stock_post')) {
                $table->integer('stock_post')->default(0)->after('stock_pre');
            }

            // 🔹 Usuario responsable
            if (!Schema::hasColumn('movimientos', 'usuario_id')) {
                $table->foreignId('usuario_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('stock_post');
            }

            // 🔹 Nombre (por si querés describir el movimiento)
            if (!Schema::hasColumn('movimientos', 'nombre')) {
                $table->string('nombre', 255)->nullable()->after('usuario_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_movimiento_id');
            $table->dropConstrainedForeignId('usuario_id');
            $table->dropColumn(['stock_pre', 'stock_post', 'nombre']);
        });
    }
};
