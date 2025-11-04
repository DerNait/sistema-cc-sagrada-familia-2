<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagosEmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $pagos = DB::table('pagos_empleados')
            ->join('empleados', 'empleados.id', '=', 'pagos_empleados.empleado_id')
            ->select(
                'pagos_empleados.id',
                'empleados.nombre',
                'pagos_empleados.fecha_ingreso',
                'pagos_empleados.salario_base',
                'pagos_empleados.bonificacion_ley',
                'pagos_empleados.bonificacion_extra',
                'pagos_empleados.descuento_igss',
                'pagos_empleados.descuentos_varios',
                'pagos_empleados.total'
            )
            ->orderBy('empleados.nombre')
            ->paginate(15);

        return response()->json($pagos);
    }

    public function show($id)
    {
        $pago = DB::table('pagos_empleados')
            ->join('empleados', 'empleados.id', '=', 'pagos_empleados.empleado_id')
            ->select(
                'pagos_empleados.id',
                'empleados.nombre',
                'pagos_empleados.fecha_ingreso',
                'pagos_empleados.salario_base',
                'pagos_empleados.bonificacion_ley',
                'pagos_empleados.bonificacion_extra',
                'pagos_empleados.descuento_igss',
                'pagos_empleados.descuentos_varios',
                'pagos_empleados.total'
            )
            ->where('pagos_empleados.id', $id)
            ->first();

        if (!$pago) {
            return response()->json(['error' => 'Pago no encontrado.'], 404);
        }

        return response()->json($pago);
    }
}
