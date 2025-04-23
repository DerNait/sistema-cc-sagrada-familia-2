<?php

namespace Database\Seeders;

use App\Models\TipoAjuste;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoAjustesSeeder extends Seeder
{
    public function run()
    {
        TipoAjuste::truncate();
        TipoAjuste::insert([
            // Bonificaciones
            ['ajuste' => 'Bono asistencia'],
            ['ajuste' => 'Bono puntualidad'],
            ['ajuste' => 'Horas extra'],
            ['ajuste' => 'Bono productividad'],
            ['ajuste' => 'Bono navideño'],
            ['ajuste' => 'Incentivo mensual'],

            // Descuentos
            ['ajuste' => 'Descuento por atraso'],
            ['ajuste' => 'Descuento por ausencia'],
            ['ajuste' => 'Préstamo'],
            ['ajuste' => 'Anticipo de salario'],
            ['ajuste' => 'Sanción disciplinaria'],
            ['ajuste' => 'Deducción por equipo dañado'],
        ]);
    }
}