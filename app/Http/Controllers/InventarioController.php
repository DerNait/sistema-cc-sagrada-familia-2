<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Movimiento;
use Carbon\Carbon;

class InventarioController extends Controller
{
    public function index()
    {
        // Cargar todos los productos para el select
        $productos = Producto::select('id', 'nombre')->get();

        $params = [
            'productos' => $productos
        ];

        return view('component', [
            'component' => 'inventarioo',
            'params'    => $params,
        ]);
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'required|string|max:255'
        ]);

        try {
            // Si es una salida, verificar que hay suficiente stock
            if ($request->tipo === 'salida') {
                $producto = Producto::find($request->producto_id);
                if ($producto->cantidad < $request->cantidad) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay suficiente stock disponible. Stock actual: ' . $producto->cantidad
                    ], 400);
                }
            }

            // Crear el movimiento
            $movimiento = Movimiento::create([
                'producto_id' => $request->producto_id,
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'descripcion' => $request->descripcion,
                'fecha' => Carbon::now()
            ]);

            // Actualizar el stock del producto
            $producto = Producto::find($request->producto_id);
            if ($request->tipo === 'entrada') {
                $producto->cantidad += $request->cantidad;
            } else {
                $producto->cantidad -= $request->cantidad;
            }
            $producto->save();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado exitosamente',
                'movimiento' => $movimiento->load('producto')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductoStock($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return response()->json([
                'stock' => $producto->cantidad
            ]);
        }
        return response()->json(['stock' => 0]);
    }
}