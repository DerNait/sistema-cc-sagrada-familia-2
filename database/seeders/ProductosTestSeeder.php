<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Movimiento;
use Carbon\Carbon;

class ProductoMovimientoSeeder extends Seeder
{
    public function run()
    {
        // Crear productos de ejemplo
        $producto1 = Producto::create([
            'tipo_producto_id' => 1, // Ajusta según datos reales en tu tabla tipo_productos
            'nombre' => 'Producto A',
            'fecha_ingreso' => Carbon::now()->subDays(10),
            'precio_unitario' => 100.00,
            'cantidad' => 50,
        ]);

        $producto2 = Producto::create([
            'tipo_producto_id' => 1,
            'nombre' => 'Producto B',
            'fecha_ingreso' => Carbon::now()->subDays(5),
            'precio_unitario' => 150.00,
            'cantidad' => 30,
        ]);

        // Registrar movimientos de entrada y salida para Producto A
        Movimiento::create([
            'producto_id' => $producto1->id,
            'tipo' => 'entrada',
            'cantidad' => 50,
            'descripcion' => 'Stock inicial producto A',
            'fecha' => Carbon::now()->subDays(10),
        ]);

        Movimiento::create([
            'producto_id' => $producto1->id,
            'tipo' => 'salida',
            'cantidad' => 10,
            'descripcion' => 'Venta de producto A',
            'fecha' => Carbon::now()->subDays(2),
        ]);

        // Registrar movimientos de entrada y salida para Producto B
        Movimiento::create([
            'producto_id' => $producto2->id,
            'tipo' => 'entrada',
            'cantidad' => 30,
            'descripcion' => 'Stock inicial producto B',
            'fecha' => Carbon::now()->subDays(5),
        ]);

        Movimiento::create([
            'producto_id' => $producto2->id,
            'tipo' => 'salida',
            'cantidad' => 5,
            'descripcion' => 'Venta de producto B',
            'fecha' => Carbon::now()->subDay(),
        ]);
    }
}