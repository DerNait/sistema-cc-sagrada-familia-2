<?php

namespace Database\Seeders;

use App\Models\TipoEstado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoEstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        TipoEstado::truncate();
        TipoEstado::insert([
            ['tipo' => 'Pendiente'],
            ['tipo' => 'Generado'],
            ['tipo' => 'Procesado'],
            ['tipo' => 'Pagado'],
            ['tipo' => 'Anulado'],
        ]);
    }
}
