<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* =======================================================================
         * 2. secciones.maestro_guia_id -> empleados.id  (opcional => NULL)
         * ==================================================================== */
        if (Schema::hasColumn('secciones', 'mestro_guia_id')) {
            Schema::table('secciones', fn (Blueprint $t) =>
                $t->renameColumn('mestro_guia_id', 'maestro_guia_id')
            );
        }

        Schema::table('secciones', fn (Blueprint $t) =>
            $t->dropForeign(['mestro_guia_id'])
        );

        DB::statement("
            UPDATE secciones s
            SET    maestro_guia_id = emp.id
            FROM   empleados emp
            WHERE  emp.usuario_id = s.maestro_guia_id
        ");

        // pone a NULL los que no hagan match
        DB::statement("
            UPDATE secciones
            SET    maestro_guia_id = NULL
            WHERE  maestro_guia_id IS NOT NULL
            AND    NOT EXISTS (
                SELECT 1 FROM empleados e
                WHERE  e.id = secciones.maestro_guia_id
            )
        ");

        Schema::table('secciones', fn (Blueprint $t) =>
            $t->foreign('maestro_guia_id')
              ->references('id')->on('empleados')
              ->nullOnDelete()
        );

        /* =======================================================================
         * 3. maestro_cursos.maestro_id -> empleados.id  (vital => elimina fila)
         * ==================================================================== */
        Schema::table('maestro_cursos', fn (Blueprint $t) =>
            $t->dropForeign(['maestro_id'])
        );

        DB::statement("
            UPDATE maestro_cursos mc
            SET    maestro_id = emp.id
            FROM   empleados emp
            WHERE  emp.usuario_id = mc.maestro_id
        ");

        DB::statement("
            DELETE FROM maestro_cursos
            WHERE  NOT EXISTS (
                SELECT 1 FROM empleados e
                WHERE  e.id = maestro_cursos.maestro_id
            )
        ");

        Schema::table('maestro_cursos', fn (Blueprint $t) =>
            $t->foreign('maestro_id')
              ->references('id')->on('empleados')
              ->cascadeOnDelete()
        );

        /* =======================================================================
         * 4. pedidos.vendedor_id -> empleados.id  (opcional => NULL)
         * ==================================================================== */
        Schema::table('pedidos', fn (Blueprint $t) =>
            $t->dropForeign(['vendedor_id'])
        );

        DB::statement("
            UPDATE pedidos p
            SET    vendedor_id = emp.id
            FROM   empleados emp
            WHERE  emp.usuario_id = p.vendedor_id
        ");

        DB::statement("
            DELETE FROM pedidos
            WHERE NOT EXISTS (
                SELECT 1 FROM empleados e
                WHERE  e.id = pedidos.vendedor_id
            )
        ");

        Schema::table('pedidos', fn (Blueprint $t) =>
            $t->foreign('vendedor_id')
              ->references('id')->on('empleados')
              ->nullOnDelete()
        );

        /* =======================================================================
         * 5. estudiante_pagos.estudiante_id -> estudiantes.id  (vital => elimina)
         * ==================================================================== */
        Schema::table('estudiante_pagos', fn (Blueprint $t) =>
            $t->dropForeign(['estudiante_id'])
        );

        DB::statement("
            UPDATE estudiante_pagos ep
            SET    estudiante_id = e.id
            FROM   estudiantes e
            WHERE  e.usuario_id = ep.estudiante_id
        ");

        DB::statement("
            DELETE FROM estudiante_pagos
            WHERE  NOT EXISTS (
                SELECT 1 FROM estudiantes e
                WHERE  e.id = estudiante_pagos.estudiante_id
            )
        ");

        Schema::table('estudiante_pagos', fn (Blueprint $t) =>
            $t->foreign('estudiante_id')
              ->references('id')->on('estudiantes')
              ->cascadeOnDelete()
        );

        /* =======================================================================
         * 6. estudiante_contactos.estudiante_id -> estudiantes.id (vital => elimina)
         * ==================================================================== */
        Schema::table('estudiante_contactos', fn (Blueprint $t) =>
            $t->dropForeign(['estudiante_id'])
        );

        DB::statement("
            UPDATE estudiante_contactos ec
            SET    estudiante_id = e.id
            FROM   estudiantes e
            WHERE  e.usuario_id = ec.estudiante_id
        ");

        DB::statement("
            DELETE FROM estudiante_contactos
            WHERE  NOT EXISTS (
                SELECT 1 FROM estudiantes e
                WHERE  e.id = estudiante_contactos.estudiante_id
            )
        ");

        Schema::table('estudiante_contactos', fn (Blueprint $t) =>
            $t->foreign('estudiante_id')
              ->references('id')->on('estudiantes')
              ->cascadeOnDelete()
        );

        /* =======================================================================
         * 7. pagos_empleados.empleado_id -> empleados.id  (vital => elimina)
         * ==================================================================== */
        Schema::table('pagos_empleados', fn (Blueprint $t) =>
            $t->dropForeign(['empleado_id'])
        );

        DB::statement("
            UPDATE pagos_empleados pe
            SET    empleado_id = emp.id
            FROM   empleados emp
            WHERE  emp.usuario_id = pe.empleado_id
        ");

        DB::statement("
            DELETE FROM pagos_empleados
            WHERE  NOT EXISTS (
                SELECT 1 FROM empleados e
                WHERE  e.id = pagos_empleados.empleado_id
            )
        ");

        Schema::table('pagos_empleados', fn (Blueprint $t) =>
            $t->foreign('empleado_id')
              ->references('id')->on('empleados')
              ->cascadeOnDelete()
        );
    }

    /* ------------------------------------------------------------------ */
    public function down(): void
    {
        // quitamos únicamente las FK para permitir rollback limpio
        foreach ([
            ['secciones',           'maestro_guia_id'],
            ['maestro_cursos',      'maestro_id'],
            ['pedidos',             'vendedor_id'],
            ['estudiante_pagos',    'estudiante_id'],
            ['estudiante_contactos','estudiante_id'],
            ['pagos_empleados',     'empleado_id'],
        ] as [$table, $column]) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column]));
        }
    }
};
