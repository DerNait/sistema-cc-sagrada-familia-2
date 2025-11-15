<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;

class LogTestController extends Controller
{
    public function registrarEntrada($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        Movimiento::create([
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => 10,
            'descripcion' => 'Carga de prueba (entrada)',
        ]);

        return "La entrada de inventario se registró correctamente.";
    }

    public function registrarSalida($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        Movimiento::create([
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => 5,
            'descripcion' => 'Venta simulada (salida)',
        ]);

        return "Salida registrada correctamente.";
    }

    public function verMovimientos($productoId)
    {
        $producto = Producto::with('movimientos')->findOrFail($productoId);
        return response()->json($producto->movimientos);
    }
}