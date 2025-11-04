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

    public function store(Request $request)
    {
        $rules = [
            'empleado_id'        => ['required', 'exists:empleados,id'],
            'periodo_mes'        => ['required', 'integer', 'min:1', 'max:12'],
            'periodo_anio'       => ['required', 'integer', 'min:2000'],
            'salario_base'       => ['required', 'numeric', 'min:0'],
            'bonificacion_ley'   => ['nullable', 'numeric', 'min:0'],
            'bonificacion_extra' => ['nullable', 'numeric', 'min:0'],
            'descuento_igss'     => ['nullable', 'numeric', 'min:0'],
            'descuentos_varios'  => ['nullable', 'numeric', 'min:0'],
            'tipo_estado_id'     => ['sometimes', 'exists:tipo_estados,id'],
        ];

        $data = $request->validate($rules);

        // Buscar si ya existe el pago para ese empleado y periodo
        $existing = DB::table('pagos_empleados')
            ->where('empleado_id', $data['empleado_id'])
            ->where('periodo_mes', $data['periodo_mes'])
            ->where('periodo_anio', $data['periodo_anio'])
            ->first();

        $total = 
            ($data['salario_base'] ?? 0) +
            ($data['bonificacion_ley'] ?? 0) +
            ($data['bonificacion_extra'] ?? 0) -
            ($data['descuento_igss'] ?? 0) -
            ($data['descuentos_varios'] ?? 0);

        $data['total'] = $total;
        $data['tipo_estado_id'] = $data['tipo_estado_id'] ?? 1; // pendiente por defecto
        $data['updated_at'] = now();

        if ($existing) {
            DB::table('pagos_empleados')
                ->where('id', $existing->id)
                ->update($data);

            return response()->json([
                'message' => 'Pago actualizado correctamente',
                'id' => $existing->id
            ]);
        }

        $data['created_at'] = now();
        $id = DB::table('pagos_empleados')->insertGetId($data);

        return response()->json([
            'message' => 'Pago creado correctamente',
            'id' => $id
        ], 201);
    }

     public function update(Request $request, $id)
    {
        $pago = DB::table('pagos_empleados')->where('id', $id)->first();

        if (!$pago) {
            return response()->json(['error' => 'Pago no encontrado.'], 404);
        }

        $rules = [
            'salario_base'       => ['sometimes', 'numeric', 'min:0'],
            'bonificacion_ley'   => ['sometimes', 'numeric', 'min:0'],
            'bonificacion_extra' => ['sometimes', 'numeric', 'min:0'],
            'descuento_igss'     => ['sometimes', 'numeric', 'min:0'],
            'descuentos_varios'  => ['sometimes', 'numeric', 'min:0'],
            'tipo_estado_id'     => ['sometimes', 'exists:tipo_estados,id'],
        ];

        $data = $request->validate($rules);

        if (empty($data)) {
            return response()->json(['error' => 'No hay cambios que aplicar.'], 422);
        }

        $merged = array_merge((array)$pago, $data);

        $data['total'] =
            ($merged['salario_base'] ?? 0) +
            ($merged['bonificacion_ley'] ?? 0) +
            ($merged['bonificacion_extra'] ?? 0) -
            ($merged['descuento_igss'] ?? 0) -
            ($merged['descuentos_varios'] ?? 0);

        $data['updated_at'] = now();

        DB::table('pagos_empleados')->where('id', $id)->update($data);

        return response()->json([
            'message' => 'Pago actualizado correctamente',
            'id' => $id
        ]);
    }
}
