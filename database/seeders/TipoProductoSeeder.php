<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProductoSeeder extends Seeder
{
    public function run()
    {
        DB::table('tipo_productos')->insert([
            ['nombre' => 'Lapices', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuadernos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Uniforme', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}