<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;

class InventarioHistorialController extends Controller
{
    /**
     * Muestra la vista del historial de movimientos de inventario.
     */
    public function index()
    {
        // Cargar todos los movimientos con sus relaciones
        $movimientos = Movimiento::with(['producto', 'tipoMovimiento', 'usuario'])
            ->orderByDesc('fecha')
            ->get();

        // Devolver la vista específica de historial
        return view('inventariohistorial', compact('movimientos'));
    }
}
