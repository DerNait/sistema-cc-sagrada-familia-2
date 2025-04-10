<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run()
    {   
        Role::truncate();
        Role::insert([
            [
                'nombre' => 'Administracion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Secretaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Inventario',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Docente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Estudiante',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
