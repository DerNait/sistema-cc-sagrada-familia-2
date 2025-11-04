<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TipoMovimientosSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $datos = [
            ['id' => 1, 'tipo' => 'Venta bolsa de útiles',        'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'tipo' => 'Venta de articulos sueltos',   'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'tipo' => 'Venta uniforme',               'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'tipo' => 'Devolución',                   'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'tipo' => 'Ajuste de inventario',         'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'tipo' => 'Compra a proveedor',           'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'tipo' => 'Consumo interno',              'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'tipo' => 'Donación',                     'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('tipo_movimientos')->upsert(
            $datos,
            ['tipo'],       // clave única
            ['updated_at']  // columnas a actualizar si existe
        );
    }
}
