<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Schema;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
      //  $faker->unique(true, 100000);
        
        // Clear tables
        $tables = [
            'ajustes_salariales', 'pagos_empleados', 'estudiante_pagos',
            'pedido_detalles', 'pedidos', 'seccion_estudiantes',
            'maestro_cursos', 'actividades', 'estudiantes_notas',
            'estudiantes', 'empleados', 'users', 'roles',
            'rol_modulos_permisos', 'modulos_permisos', 'modulos', 'menus',
            'grado_cursos', 'secciones', 'grado', 'cursos',
            'productos', 'tipo_productos', 'bolsas', 'bolsas_detalles',
            'tipo_pagos', 'tipo_estados', 'tipo_ajustes', 'becas',
            'grado_precio', 'estudiante_contactos', 'movimientos'
        ];
        
        foreach($tables as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->truncate();
            }
        }

        // 1. Roles (5 roles)
        $roles = ['Administracion', 'Secretaria', 'Inventario', 'Docente', 'Estudiante'];
        foreach($roles as $i => $r) {
            DB::table('roles')->insert([
                'id' => $i + 1,
                'nombre' => $r,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 2. Admin user
        DB::table('users')->insert([
            'id' => 1,
            'rol_id' => 1,
            'name' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@escuela.com',
            'password' => Hash::make('secret'),
            'fecha_registro' => now(),
            'fecha_nacimiento' => Carbon::now()->subYears(30),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Becas (3 types)
        DB::table('becas')->insert([
            ['nombre' => 'Completa', 'descuento' => 100],
            ['nombre' => 'Media', 'descuento' => 50],
            ['nombre' => 'Parcial', 'descuento' => 25]
        ]);

        // 4. Grados (12 grades - double the original)
        foreach(range(1, 12) as $g) {
            DB::table('grado')->insert([
                'id' => $g,
                'nombre' => "grado $g",
                '2025' => Carbon::now()->year,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Grado Precios
            DB::table('grado_precio')->insert([
                'grado_id' => $g,
                'mensualidad' => $faker->randomFloat(2, 500, 1500),
                'inscripcion' => $faker->randomFloat(2, 200, 800),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 5. Cursos (10 per grade - double the original)
        $cursoId = 1;
        foreach(range(1, 12) as $g) {
            for($j = 1; $j <= 10; $j++) {
                $cursoName = Str::title($faker->unique()->word) . " G$g";
                DB::table('cursos')->insert([
                    'id' => $cursoId,
                    'nombre' => $cursoName,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('grado_cursos')->insert([
                    'grado_id' => $g,
                    'curso_id' => $cursoId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $cursoId++;
            }
        }

        // 6. Staff Users (100 teachers + 20 admin/support staff)
        $teacherIds = [];
        $staffIds = [];
        
        // Teachers (100)
        for($i = 2; $i <= 101; $i++) {
            DB::table('users')->insert([
                'id' => $i,
                'rol_id' => 4, // Docente
                'name' => $faker->firstName,
                'apellido' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'fecha_registro' => $faker->dateTimeBetween('-5 years'),
                'fecha_nacimiento' => $faker->date('Y-m-d', '-25 years'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $teacherIds[] = $i;
        }
        
        // Support staff (20)
        for($i = 102; $i <= 121; $i++) {
            $rolId = $faker->randomElement([2, 3]); // Secretaria or Inventario
            DB::table('users')->insert([
                'id' => $i,
                'rol_id' => $rolId,
                'name' => $faker->firstName,
                'apellido' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'fecha_registro' => $faker->dateTimeBetween('-5 years'),
                'fecha_nacimiento' => $faker->date('Y-m-d', '-25 years'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $staffIds[] = $i;
        }

        // 7. Secciones (3 per grade - 36 total)
        $sectionId = 1;
        foreach(range(1, 12) as $g) {
            foreach(['A', 'B', 'C'] as $letra) {
                DB::table('secciones')->insert([
                    'id' => $sectionId,
                    'grado_id' => $g,
                    'mestro_guia_id' => $faker->randomElement($teacherIds),
                    'seccion' => $letra,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $sectionId++;
            }
        }

        // 8. Maestro_cursos assignments
        foreach(range(1, 36) as $sec) {
            $gradoId = DB::table('secciones')->where('id', $sec)->value('grado_id');
            $courses = DB::table('grado_cursos')->where('grado_id', $gradoId)->get();
            
            foreach($courses as $course) {
                DB::table('maestro_cursos')->insert([
                    'maestro_id' => $faker->randomElement($teacherIds),
                    'curso_id' => $course->curso_id,
                    'seccion_id' => $sec,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 9. Students (50 per section - 1,800 total)
        $stuStart = 122; // After staff users
        foreach(range(1, 36) as $sec) {
            for($k = 1; $k <= 50; $k++) {
                $uid = $stuStart++;
                DB::table('users')->insert([
                    'id' => $uid,
                    'rol_id' => 5, // Estudiante
                    'name' => $faker->firstName,
                    'apellido' => $faker->lastName,
                    'email' => "est$uid@escuela.com",
                    'password' => Hash::make('password'),
                    'fecha_registro' => $faker->dateTimeBetween('-4 years'),
                    'fecha_nacimiento' => $faker->date('Y-m-d', '-18 years'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('estudiantes')->insert([
                    'usuario_id' => $uid,
                    'beca_id' => $faker->randomElement([1, 2, 3]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('seccion_estudiantes')->insert([
                    'seccion_id' => $sec,
                    'estudiante_id' => $uid,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 2-3 contacts per student
                $contacts = $faker->numberBetween(2, 3);
                for($c = 0; $c < $contacts; $c++) {
                    DB::table('estudiante_contactos')->insert([
                        'estudiante_id' => $uid,
                        'nombre' => $faker->name,
                        'contacto' => $faker->phoneNumber,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // 10. Actividades (5 per course - 600 total)
        foreach(DB::table('grado_cursos')->pluck('id') as $gc) {
            for($a = 1; $a <= 5; $a++) {
                DB::table('actividades')->insert([
                    'nombre' => "Actividad $a",
                    'grado_curso_id' => $gc,
                    'created_at' => $faker->dateTimeBetween('-1 year'),
                    'updated_at' => now()
                ]);
            }
        }

        // 11. Grades for students (5-10 per student)
        $students = DB::table('seccion_estudiantes')->get();
        foreach($students as $student) {
            $seccion = DB::table('secciones')->find($student->seccion_id);
            $activities = DB::table('actividades')
                ->join('grado_cursos', 'actividades.grado_curso_id', '=', 'grado_cursos.id')
                ->where('grado_cursos.grado_id', $seccion->grado_id)
                ->get();
            
            $gradesCount = $faker->numberBetween(5, 10);
            $gradedActivities = $activities->random($gradesCount);
            
            foreach($gradedActivities as $activity) {
                DB::table('estudiantes_notas')->insert([
                    'seccion_estudiante_id' => $student->id,
                    'actividad_id' => $activity->id,
                    'nota' => $faker->randomFloat(2, 50, 100),
                    'created_at' => $activity->created_at,
                    'updated_at' => now()
                ]);
            }
        }

        // 12. Payment types, statuses, adjustments
        DB::table('tipo_pagos')->insert([
            ['nombre' => 'Efectivo'],
            ['nombre' => 'Tarjeta'],
            ['nombre' => 'Transferencia'],
            ['nombre' => 'Cheque'],
            ['nombre' => 'Depósito']
        ]);
        
        DB::table('tipo_estados')->insert([
            ['tipo' => 'Pendiente'],
            ['tipo' => 'Completado'],
            ['tipo' => 'Cancelado'],
            ['tipo' => 'Reembolsado'],
            ['tipo' => 'En proceso']
        ]);
        
        DB::table('tipo_ajustes')->insert([
            ['ajuste' => 'Bonificación'],
            ['ajuste' => 'Descuento'],
            ['ajuste' => 'Incentivo'],
            ['ajuste' => 'Deducción']
        ]);

        // 13. Staff employment records
        foreach($teacherIds as $eid) {
            DB::table('empleados')->insert([
                'usuario_id' => $eid,
                'salario_base' => $faker->randomFloat(2, 2500, 7000),
                'created_at' => $faker->dateTimeBetween('-5 years'),
                'updated_at' => now()
            ]);
        }

        // 14. Staff payments (3-12 per employee)
        foreach($teacherIds as $eid) {
            $payments = $faker->numberBetween(3, 12);
            for($p = 0; $p < $payments; $p++) {
                $year = $faker->numberBetween(2020, 2025);
                $month = $faker->numberBetween(1, 12);
                $base = $faker->randomFloat(2, 2500, 7000);
                $adjustment = $faker->randomFloat(2, -500, 1000);
                
                $pid = DB::table('pagos_empleados')->insertGetId([
                    'empleado_id' => $eid,
                    'periodo_mes' => $month,
                    'periodo_anio' => $year,
                    'tipo_estado_id' => $faker->randomElement([1, 2, 3]),
                    'monto_base' => $base,
                    'monto_total' => $base + $adjustment,
                    'created_at' => Carbon::create($year, $month, 15),
                    'updated_at' => now()
                ]);
                
                if($adjustment > 0) {
                    DB::table('ajustes_salariales')->insert([
                        'pago_empleado_id' => $pid,
                        'tipo_ajuste_id' => $faker->randomElement([1, 3]), // Bonificación or Incentivo
                        'descripcion' => $faker->sentence,
                        'monto' => $adjustment,
                        'created_at' => Carbon::create($year, $month, 15),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // 15. Products (200 products)
$productTypes = [];
for($i = 1; $i <= 20; $i++) {
    $productTypes[] = DB::table('tipo_productos')->insertGetId([
        'nombre' => Str::title($faker->word) . ' Type',  // E.g., "Cuaderno Type"
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

$productIds = [];
for($i = 1; $i <= 200; $i++) {
    $productIds[] = DB::table('productos')->insertGetId([
        'tipo_producto_id' => $faker->randomElement($productTypes),
        'nombre' => 'Product-' . $i . '-' . Str::title($faker->word), // E.g., "Product-1-Libro"
        'fecha_ingreso' => $faker->dateTimeBetween('-2 years'),
        'precio_unitario' => $faker->randomFloat(2, 1, 500),
        'cantidad' => $faker->numberBetween(0, 200),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // Initial stock movement
    DB::table('movimientos')->insert([
        'producto_id' => $i,
        'tipo' => 'entrada',
        'cantidad' => $faker->numberBetween(50, 200),
        'descripcion' => 'Stock inicial',
        'fecha' => $faker->dateTimeBetween('-2 years')
    ]);
}

        // 16. Bags (50 bags)
        $bagIds = [];
        for($i = 1; $i <= 50; $i++) {
            $bagIds[] = DB::table('bolsas')->insertGetId([
                'nombre' => "Bolsa $i - " . Str::title($faker->word),
                'precio_total' => $faker->randomFloat(2, 50, 1000),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 3-8 products per bag
            $productsInBag = $faker->numberBetween(3, 8);
            $selectedProducts = $faker->randomElements($productIds, $productsInBag);
            
            foreach($selectedProducts as $prodId) {
                DB::table('bolsas_detalles')->insert([
                    'bolsa_id' => $i,
                    'producto_id' => $prodId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 17. Orders (500 orders)
$allUserIds = array_merge([1], $teacherIds, $staffIds, range(122, $stuStart-1));

for($i = 1; $i <= 500; $i++) {
    $orderDate = $faker->dateTimeBetween('-2 years');
    $userId = $faker->randomElement($allUserIds);
    $sellerId = $faker->randomElement(array_merge([1], $teacherIds, $staffIds));
    
    $pid = DB::table('pedidos')->insertGetId([
        'usuario_id' => $userId,
        'vendedor_id' => $sellerId,
        'precio_total' => 0, // Will be calculated
        'created_at' => $orderDate,
        'updated_at' => now()
    ]);
    
    $total = 0;
    $items = $faker->numberBetween(1, 5);
    $selectedProducts = $faker->randomElements($productIds, $items);
    
    foreach($selectedProducts as $prodId) {
        $quantity = $faker->numberBetween(1, 5);
        $product = DB::table('productos')->find($prodId);
        $price = $product->precio_unitario;
        $subtotal = $quantity * $price;
        $total += $subtotal;

        // Fixed: Properly closed the optional() closure
        $bolsaId = $faker->optional(0.7) ? $faker->randomElement($bagIds) : null;
        
        DB::table('pedido_detalles')->insert([
            'pedido_id' => $pid,
            'producto_id' => $prodId,
            'bolsa_id' => $bolsaId,
            'created_at' => $orderDate,
            'updated_at' => now()
        ]);
        
        // Record movement
        DB::table('movimientos')->insert([
            'producto_id' => $prodId,
            'tipo' => 'salida',
            'cantidad' => $quantity,
            'descripcion' => 'Venta #' . $pid,
            'fecha' => $orderDate
        ]);
    }
    
    // Update order total
    DB::table('pedidos')->where('id', $pid)->update([
        'precio_total' => $total
    ]);
}

        // 18. Student payments (5-12 per student)
        $students = DB::table('estudiantes')->get();
        foreach($students as $student) {
            $gradoId = DB::table('seccion_estudiantes')
                ->join('secciones', 'seccion_estudiantes.seccion_id', '=', 'secciones.id')
                ->where('seccion_estudiantes.estudiante_id', $student->usuario_id)
                ->value('secciones.grado_id');
            
            $payments = $faker->numberBetween(5, 12);
            for($p = 0; $p < $payments; $p++) {
                $months = $faker->numberBetween(1, 6);
                $startDate = $faker->dateTimeBetween('-4 years');
                $endDate = (clone $startDate)->modify("+$months months");
                
                DB::table('estudiante_pagos')->insert([
                    'grado_precio_id' => $gradoId,
                    'estudiante_id' => $student->usuario_id,
                    'tipo_pago_id' => $faker->numberBetween(1, 5),
                    'monto_pagado' => $faker->randomFloat(2, 300, 1500),
                    'meses_pagados' => $months,
                    'periodo_inicio' => $startDate,
                    'periodo_fin' => $endDate,
                    'tipo_estado_id' => $faker->randomElement([1, 2, 3, 4, 5]),
                    'created_at' => $startDate,
                    'updated_at' => now()
                ]);
            }
        }

        // 19. Navigation and permissions
        $this->call([
            NavigationSeeder::class,
            RolePermissionSeeder::class
        ]);
    }
}