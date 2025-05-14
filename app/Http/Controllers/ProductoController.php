<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'nombre' => 'nullable|string|max:255',
            'tipo_producto_id' => 'nullable|integer|exists:tipo_productos,id',
            'precio_min' => 'nullable|numeric|min:0',
            'precio_max' => 'nullable|numeric|min:0',
            'fecha_ingreso' => 'nullable|date',
        ]);

        $query = DB::table('products')
            ->select('products.*', 'tipo_productos.nombre as tipo_nombre')
            ->join('tipo_productos', 'products.tipo_producto_id', '=', 'tipo_productos.id');

        if ($request->filled('id')) {
            $query->where('products.id', $request->id);
        }

        if ($request->filled('nombre')) {
            $query->where('products.nombre', 'like', '%'.$request->nombre.'%');
        }

        if ($request->filled('tipo_producto_id')) {
            $query->where('products.tipo_producto_id', $request->tipo_producto_id);
        }

        if ($request->filled('precio_min')) {
            $query->where('products.precio_unitario', '>=', $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('products.precio_unitario', '<=', $request->precio_max);
        }

        if ($request->filled('fecha_ingreso')) {
            $query->whereDate('products.fecha_ingreso', $request->fecha_ingreso);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function tipos()
    {
        return response()->json(
            DB::table('tipo_productos')
                ->select('id', 'nombre')
                ->get()
        );
    }
}