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
            ['tipo' => 'Completado'],
            ['tipo' => 'Cancelado'],
            ['tipo' => 'Reembolsado'],
            ['tipo' => 'En proceso'],
        ]);
    }
}
