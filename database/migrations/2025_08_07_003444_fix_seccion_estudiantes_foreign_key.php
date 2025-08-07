<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quita la FK rota (si existiera)
        Schema::table('seccion_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
        });

        // 2. Re-asigna estudiante_id ⇒ estudiante.id
        //    (user_id → buscamos estudiante.usuario_id = user_id)
        DB::statement("
            UPDATE seccion_estudiantes se
            SET estudiante_id = e.id
            FROM estudiantes e
            WHERE e.usuario_id = se.estudiante_id
        ");

        // 3. Borra filas sin match (estudiante_id quedó NULL)
        DB::statement("DELETE FROM seccion_estudiantes WHERE estudiante_id IS NULL");

        // 4. Evita duplicados (misma sección + mismo estudiante)
        DB::statement("
            DELETE FROM seccion_estudiantes se1
            USING seccion_estudiantes se2
            WHERE se1.id < se2.id
              AND se1.seccion_id   = se2.seccion_id
              AND se1.estudiante_id = se2.estudiante_id
        ");

        // 5. Vuelve a poner la FK correcta
        Schema::table('seccion_estudiantes', function (Blueprint $table) {
            $table->foreign('estudiante_id')
                  ->references('id')
                  ->on('estudiantes')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seccion_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
        });
    }
};
