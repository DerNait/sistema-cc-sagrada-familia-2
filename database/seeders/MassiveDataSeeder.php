<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        /* =============================================================
         * 0. Limpieza de tablas (respeta el orden de claves foráneas)
         * =========================================================== */
        $tables = [
            'ajustes_salariales', 'pagos_empleados', 'estudiante_pagos',
            'pedido_detalles', 'movimientos', 'pedidos',
            'seccion_estudiantes', 'maestro_cursos',
            'estudiantes_notas', 'actividades',
            'estudiantes', 'empleados', 'users', 'roles',
            'rol_modulos_permisos', 'modulos_permisos', 'modulos', 'menus',
            'grado_cursos', 'secciones', 'grado', 'cursos', 'grado_precio',
            'productos', 'tipo_productos', 'bolsas', 'bolsas_detalles',
            'tipo_pagos', 'tipo_estados', 'tipo_ajustes', 'becas',
            'estudiante_contactos'
        ];
        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->truncate();
            }
        }

        /* =============================================================
         * 1. Roles base
         * =========================================================== */
        $roles = ['Administracion', 'Secretaria', 'Inventario', 'Docente', 'Estudiante'];
        foreach ($roles as $idx => $rol) {
            DB::table('roles')->insert([
                'id'         => $idx + 1,
                'nombre'     => $rol,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /* =============================================================
         * 2. Usuario admin
         * =========================================================== */
        DB::table('users')->insert([
            'id'              => 1,
            'rol_id'          => 1,
            'name'            => 'Admin',
            'apellido'        => 'Sistema',
            'email'           => 'admin@escuela.com',
            'password'        => Hash::make('secret'),
            'fecha_registro'  => now(),
            'fecha_nacimiento'=> Carbon::now()->subYears(30),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        /* =============================================================
         * 3. Becas
         * =========================================================== */
        DB::table('becas')->insert([
            ['nombre' => 'Completa', 'descuento' => 100],
            ['nombre' => 'Media',    'descuento' => 50],
            ['nombre' => 'Parcial',  'descuento' => 25],
        ]);

        /* =============================================================
         * 4. Grados + precios
         * =========================================================== */
        foreach (range(1, 12) as $g) {
            DB::table('grado')->insert([
                'id'         => $g,
                'nombre'     => "Grado $g",
                'year'       => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('grado_precio')->insert([
                'grado_id'   => $g,
                'mensualidad'=> $faker->randomFloat(2, 500, 1500),
                'inscripcion'=> $faker->randomFloat(2, 200, 800),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /* =============================================================
         * 5. Cursos y grado_cursos
         * =========================================================== */
        $cursoId = 1;
        foreach (range(1, 12) as $g) {
            for ($j = 1; $j <= 10; $j++) {
                $name = Str::title($faker->unique()->word) . " G$g";
                DB::table('cursos')->insert([
                    'id'         => $cursoId,
                    'nombre'     => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('grado_cursos')->insert([
                    'grado_id'   => $g,
                    'curso_id'   => $cursoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $cursoId++;
            }
        }

        /* =============================================================
         * 6. Personal docente y staff
         * =========================================================== */
        $teacherUserIds = $employeeIds = $userToEmp = [];
        // Docentes
        for ($i = 2; $i <= 101; $i++) {
            DB::table('users')->insert([
                'id'              => $i,
                'rol_id'          => 4,
                'name'            => $faker->firstName,
                'apellido'        => $faker->lastName,
                'email'           => $faker->unique()->safeEmail,
                'password'        => Hash::make('password'),
                'fecha_registro'  => $faker->dateTimeBetween('-5 years'),
                'fecha_nacimiento'=> $faker->date('Y-m-d', '-25 years'),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            $teacherUserIds[] = $i;
            $empId = DB::table('empleados')->insertGetId([
                'usuario_id'  => $i,
                'salario_base'=> $faker->randomFloat(2, 2500, 7000),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $employeeIds[]   = $empId;
            $userToEmp[$i]   = $empId;
        }

        // Staff (secretaría/inventario) sin registro en empleados
        $staffUserIds = [];
        for ($i = 102; $i <= 121; $i++) {
            $rol = $faker->randomElement([2, 3]);
            DB::table('users')->insert([
                'id'              => $i,
                'rol_id'          => $rol,
                'name'            => $faker->firstName,
                'apellido'        => $faker->lastName,
                'email'           => $faker->unique()->safeEmail,
                'password'        => Hash::make('password'),
                'fecha_registro'  => $faker->dateTimeBetween('-5 years'),
                'fecha_nacimiento'=> $faker->date('Y-m-d', '-25 years'),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            $staffUserIds[] = $i;
        }

        /* =============================================================
         * 7. Secciones
         * =========================================================== */
        $sectionId = 1;
        foreach (range(1, 12) as $grade) {
            foreach (['A', 'B', 'C'] as $letter) {
                DB::table('secciones')->insert([
                    'id'              => $sectionId,
                    'grado_id'        => $grade,
                    'maestro_guia_id' => $faker->randomElement($employeeIds),
                    'seccion'         => $letter,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $sectionId++;
            }
        }

        /* =============================================================
         * 8. maestro_cursos (empleado FK)
         * =========================================================== */
        foreach (range(1, 36) as $secId) {
            $gradoId = DB::table('secciones')->where('id', $secId)->value('grado_id');
            $courses = DB::table('grado_cursos')->where('grado_id', $gradoId)->pluck('curso_id');
            foreach ($courses as $cId) {
                DB::table('maestro_cursos')->insert([
                    'maestro_id' => $faker->randomElement($employeeIds),
                    'curso_id'   => $cId,
                    'seccion_id' => $secId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        /* =============================================================
         * 9. Estudiantes y pivote seccion_estudiantes
         * =========================================================== */
        $studentUserIds = $studentIds = $userToStu = [];
        $nextUserId = 122;
        foreach (range(1, 36) as $secId) {
            for ($k = 1; $k <= 50; $k++) {
                $uid = $nextUserId++;
                // users
                DB::table('users')->insert([
                    'id'              => $uid,
                    'rol_id'          => 5,
                    'name'            => $faker->firstName,
                    'apellido'        => $faker->lastName,
                    'email'           => "est$uid@escuela.com",
                    'password'        => Hash::make('password'),
                    'fecha_registro'  => $faker->dateTimeBetween('-4 years'),
                    'fecha_nacimiento'=> $faker->date('Y-m-d', '-18 years'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $studentUserIds[] = $uid;
                // estudiantes
                $estId = DB::table('estudiantes')->insertGetId([
                    'usuario_id' => $uid,
                    'beca_id'    => $faker->randomElement([1, 2, 3]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $studentIds[]      = $estId;
                $userToStu[$uid]   = $estId;
                // pivote
                DB::table('seccion_estudiantes')->insert([
                    'seccion_id'     => $secId,
                    'estudiante_id'  => $estId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // contactos
                foreach (range(1, $faker->numberBetween(2, 3)) as $_) {
                    DB::table('estudiante_contactos')->insert([
                        'estudiante_id' => $estId,
                        'nombre'        => $faker->name,
                        'contacto'      => $faker->phoneNumber,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }

        /* =============================================================
         * 10. Actividades con nuevas columnas
         * =========================================================== */
        foreach (DB::table('grado_cursos')->pluck('id') as $gcId) {
            for ($a = 1; $a <= 5; $a++) {
                $inicio = $faker->dateTimeBetween('-10 months');
                DB::table('actividades')->insert([
                    'nombre'           => "Actividad $a",
                    'grado_curso_id'   => $gcId,
                    'total'            => 100,
                    'fecha_inicio'     => $inicio,
                    'fecha_fin'        => (clone $inicio)->modify('+10 days'),
                    'created_at'       => $inicio,
                    'updated_at'       => now(),
                ]);
            }
        }

        /* =============================================================
         * 11. Notas de estudiantes con comentario
         * =========================================================== */
        $seccionEsts = DB::table('seccion_estudiantes')->get();
        foreach ($seccionEsts as $row) {
            $seccion = DB::table('secciones')->find($row->seccion_id);
            $activities = DB::table('actividades')
                ->join('grado_cursos', 'actividades.grado_curso_id', '=', 'grado_cursos.id')
                ->where('grado_cursos.grado_id', $seccion->grado_id)
                ->pluck('actividades.id');

            foreach ($faker->randomElements($activities->toArray(), $faker->numberBetween(5, 10)) as $actId) {
                DB::table('estudiantes_notas')->insert([
                    'seccion_estudiante_id' => $row->id,
                    'actividad_id'          => $actId,
                    'nota'                  => $faker->randomFloat(2, 50, 100),
                    'comentario'            => $faker->optional(0.7)->sentence,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }
        }

        /* =============================================================
         * 12. Catálogos necesarios antes de pagos_empleados
         * =========================================================== */
        DB::table('tipo_estados')->insert([
            ['id' => 1, 'tipo' => 'Pendiente'],
            ['id' => 2, 'tipo' => 'Completado'],
            ['id' => 3, 'tipo' => 'Cancelado'],
            ['id' => 4, 'tipo' => 'Reembolsado'],
            ['id' => 5, 'tipo' => 'En proceso'],
        ]);

        DB::table('tipo_ajustes')->insert([
            ['id' => 1, 'ajuste' => 'Bonificación'],
            ['id' => 2, 'ajuste' => 'Descuento'],
            ['id' => 3, 'ajuste' => 'Incentivo'],
            ['id' => 4, 'ajuste' => 'Deducción'],
        ]);

        /* =============================================================
         * 12. Pagos empleados (para cada empleado)
         * =========================================================== */
        foreach ($employeeIds as $empId) {
            foreach (range(1, $faker->numberBetween(3, 12)) as $_) {
                $year  = $faker->numberBetween(2020, 2025);
                $month = $faker->numberBetween(1, 12);
                $base  = $faker->randomFloat(2, 2500, 7000);
                $adjust= $faker->randomFloat(2, -500, 1000);
                $pid = DB::table('pagos_empleados')->insertGetId([
                    'empleado_id'    => $empId,
                    'periodo_mes'    => $month,
                    'periodo_anio'   => $year,
                    'tipo_estado_id' => $faker->randomElement([1,2,3]),
                    'monto_base'     => $base,
                    'monto_total'    => $base + $adjust,
                    'created_at'     => Carbon::create($year, $month, 15),
                    'updated_at'     => now(),
                ]);
                if ($adjust > 0) {
                    DB::table('ajustes_salariales')->insert([
                        'pago_empleado_id' => $pid,
                        'tipo_ajuste_id'   => $faker->randomElement([1,3]),
                        'descripcion'      => $faker->sentence,
                        'monto'            => $adjust,
                        'created_at'       => Carbon::create($year, $month, 15),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }

        /* =============================================================
         * 13. Productos, bolsas, pedidos, etc.  (sin cambios lógicos ➜ FK usuario_id)
         *     Solo asegúrate de que vendedor_id use $employeeIds cuando elija
         * =========================================================== */
        // — tipo_productos —
        $typeIds = [];
        foreach (range(1, 20) as $i) {
            $typeIds[] = DB::table('tipo_productos')->insertGetId([
                'nombre'     => Str::title($faker->word) . ' Type',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        // — productos —
        $productIds = [];
        foreach (range(1, 200) as $i) {
            $productIds[] = DB::table('productos')->insertGetId([
                'tipo_producto_id' => $faker->randomElement($typeIds),
                'nombre'           => 'Product-' . $i . '-' . Str::title($faker->word),
                'fecha_ingreso'    => $faker->dateTimeBetween('-2 years'),
                'precio_unitario'  => $faker->randomFloat(2, 1, 500),
                'cantidad'         => $faker->numberBetween(0, 200),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            DB::table('movimientos')->insert([
                'producto_id' => $productIds[$i-1],
                'tipo'        => 'entrada',
                'cantidad'    => $faker->numberBetween(50, 200),
                'descripcion' => 'Stock inicial',
                'fecha'       => $faker->dateTimeBetween('-2 years'),
            ]);
        }
        // — bolsas —
        $bagIds = [];
        foreach (range(1, 50) as $i) {
            $bagIds[] = DB::table('bolsas')->insertGetId([
                'nombre'       => "Bolsa $i - " . Str::title($faker->word),
                'precio_total' => $faker->randomFloat(2, 50, 1000),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            foreach ($faker->randomElements($productIds, $faker->numberBetween(3, 8)) as $prodId) {
                DB::table('bolsas_detalles')->insert([
                    'bolsa_id'    => $bagIds[$i-1],
                    'producto_id' => $prodId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
        // — pedidos —
        $allBuyers = array_merge([1], $teacherUserIds, $staffUserIds, $studentUserIds);
        foreach (range(1, 500) as $_) {
            $date = $faker->dateTimeBetween('-2 years');
            $buyer = $faker->randomElement($allBuyers);
            $sellerEmp = $faker->randomElement($employeeIds);
            $pedidoId = DB::table('pedidos')->insertGetId([
                'usuario_id'  => $buyer,
                'vendedor_id' => $sellerEmp,
                'precio_total'=> 0,
                'created_at'  => $date,
                'updated_at'  => now(),
            ]);
            $total = 0;
            foreach ($faker->randomElements($productIds, $faker->numberBetween(1, 5)) as $prodId) {
                $qty   = $faker->numberBetween(1, 5);
                $price = DB::table('productos')->where('id', $prodId)->value('precio_unitario');
                $total += $qty * $price;
                DB::table('pedido_detalles')->insert([
                    'pedido_id'   => $pedidoId,
                    'producto_id' => $prodId,
                    'bolsa_id'    => $faker->optional(0.7)->randomElement($bagIds),
                    'created_at'  => $date,
                    'updated_at'  => now(),
                ]);
                DB::table('movimientos')->insert([
                    'producto_id' => $prodId,
                    'tipo'        => 'salida',
                    'cantidad'    => $qty,
                    'descripcion' => 'Venta #' . $pedidoId,
                    'fecha'       => $date,
                ]);
            }
            DB::table('pedidos')->where('id', $pedidoId)->update(['precio_total' => $total]);
        }

        /* =============================================================
         * 14. estudiante_pagos con FK correcta
         * =========================================================== */
        foreach ($studentIds as $estId) {
            $gradoId = DB::table('seccion_estudiantes')
                ->join('secciones', 'seccion_estudiantes.seccion_id', '=', 'secciones.id')
                ->where('seccion_estudiantes.estudiante_id', $estId)
                ->value('secciones.grado_id');
            foreach (range(1, $faker->numberBetween(5, 12)) as $_) {
                $months = $faker->numberBetween(1, 6);
                $start  = $faker->dateTimeBetween('-4 years');
                DB::table('estudiante_pagos')->insert([
                    'grado_precio_id' => $gradoId,
                    'estudiante_id'   => $estId,
                    'tipo_pago_id'    => $faker->numberBetween(1, 5),
                    'monto_pagado'    => $faker->randomFloat(2, 300, 1500),
                    'meses_pagados'   => $months,
                    'periodo_inicio'  => $start,
                    'periodo_fin'     => (clone $start)->modify("+{$months} months"),
                    'tipo_estado_id'  => $faker->numberBetween(1, 5),
                    'created_at'      => $start,
                    'updated_at'      => now(),
                ]);
            }
        }

        /* =============================================================
         * 15. Tablas catálogo simples
         * =========================================================== */
        DB::table('tipo_pagos')->insert([
            ['nombre' => 'Efectivo'], ['nombre' => 'Tarjeta'],
            ['nombre' => 'Transferencia'], ['nombre' => 'Cheque'],
            ['nombre' => 'Depósito'],
        ]);
        DB::table('tipo_estados')->insert([
            ['tipo' => 'Pendiente'], ['tipo' => 'Completado'], ['tipo' => 'Cancelado'],
            ['tipo' => 'Reembolsado'], ['tipo' => 'En proceso'],
        ]);
        DB::table('tipo_ajustes')->insert([
            ['ajuste' => 'Bonificación'], ['ajuste' => 'Descuento'],
            ['ajuste' => 'Incentivo'], ['ajuste' => 'Deducción'],
        ]);

        /* =============================================================
         * 16. Seeders secundarios (menus, permisos…)
         * =========================================================== */
        $this->call([
            NavigationSeeder::class,
            RolePermissionSeeder::class,
        ]);

        /* =============================================================
         * 17. Reseteo de secuencias
         * =========================================================== */
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;
            DB::statement("SELECT setval(pg_get_serial_sequence('$table', 'id'), COALESCE((SELECT MAX(id)+1 FROM $table), 1), false)");
        }
    }
}
