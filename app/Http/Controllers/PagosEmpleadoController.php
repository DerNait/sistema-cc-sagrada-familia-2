<?php

namespace App\Http\Controllers;

use App\Models\PagosEmpleado;
use App\Models\TipoEstado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagosEmpleadoController extends Controller
{
    /**
     * Mostrar vista de administración de pagos de empleados
     * (para roles Admin / Secretaria)
     */
    public function index()
    {
        $user = Auth::user();
        $rolId = $user->rol_id;

        // Solo admin (1) o secretaria (2) pueden ver la vista
        if ($rolId === 1 || $rolId === 2) {
            $tiposEstado = TipoEstado::select('id', 'tipo')->orderBy('tipo')->get();

            // Pagos con relaciones
            $pagos = PagosEmpleado::with(['empleado.user', 'tipoEstado'])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($pago) {
                    $usuario = $pago->empleado->user ?? null;

                    return [
                        'id'                 => $pago->id,
                        'nombre'             => $usuario->name ?? 'N/A',
                        'apellido'           => $usuario->apellido ?? 'N/A',
                        'correo'             => $usuario->email ?? 'N/A',
                        'fecha_ingreso'      => optional($pago->fecha_ingreso)->format('Y-m-d'),
                        'salario_base'       => $pago->salario_base,
                        'bonificacion_ley'   => $pago->bonificacion_ley,
                        'bonificacion_extra' => $pago->bonificacion_extra,
                        'descuento_igss'     => $pago->descuento_igss,
                        'descuentos_varios'  => $pago->descuentos_varios,
                        'total'              => $pago->total,
                        'tipo_estado_id'     => $pago->tipo_estado_id,
                        'tipo_estado_nombre' => $pago->tipoEstado->tipo ?? 'N/A',
                    ];
                });

            $params = [
                'pagos' => $pagos,
                'tipos_estado' => $tiposEstado
            ];

            return view('component', [
                'component' => 'admin-pago-empleado',
                'params'    => $params,
            ]);
        }

        // Otros roles no autorizados
        abort(403, 'No tienes permisos para acceder a esta sección.');
    }

    /**
     * Registrar un nuevo pago de empleado
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'empleado_id'       => ['required', 'integer', 'exists:empleados,id'],
            'periodo_mes'       => ['required', 'integer', 'min:1', 'max:12'],
            'periodo_anio'      => ['required', 'integer', 'min:2000'],
            'salario_base'      => ['required', 'numeric', 'min:0.01'],
            'bonificacion_ley'  => ['nullable', 'numeric', 'min:0'],
            'bonificacion_extra'=> ['nullable', 'numeric', 'min:0'],
            'descuento_igss'    => ['nullable', 'numeric', 'min:0'],
            'descuentos_varios' => ['nullable', 'numeric', 'min:0'],
        ]);

        $total =
            ($data['salario_base'] ?? 0) +
            ($data['bonificacion_ley'] ?? 0) +
            ($data['bonificacion_extra'] ?? 0) -
            ($data['descuento_igss'] ?? 0) -
            ($data['descuentos_varios'] ?? 0);

        $data['total'] = $total;
        $data['tipo_estado_id'] = 1; // “Pendiente” por defecto
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Si ya existía pago del mismo mes/año para ese empleado → actualizarlo
        $existing = PagosEmpleado::where('empleado_id', $data['empleado_id'])
            ->where('periodo_mes', $data['periodo_mes'])
            ->where('periodo_anio', $data['periodo_anio'])
            ->first();

        if ($existing) {
            $existing->update($data);
            return response()->json([
                'message' => 'Pago actualizado correctamente.',
                'pago_id' => $existing->id,
            ]);
        }

        $pago = PagosEmpleado::create($data);

        return response()->json([
            'message' => 'Pago de empleado registrado correctamente.',
            'pago_id' => $pago->id,
            'total'   => $pago->total,
        ]);
    }

    /**
     * Mostrar detalle de un pago específico
     */
    public function show($id)
    {
        $pago = PagosEmpleado::with(['empleado.user', 'tipoEstado'])->findOrFail($id);
        $usuario = $pago->empleado->user ?? null;

        return response()->json([
            'id'                 => $pago->id,
            'nombre'             => $usuario->name ?? 'N/A',
            'apellido'           => $usuario->apellido ?? 'N/A',
            'correo'             => $usuario->email ?? 'N/A',
            'fecha_ingreso'      => optional($pago->fecha_ingreso)->format('Y-m-d'),
            'salario_base'       => $pago->salario_base,
            'bonificacion_ley'   => $pago->bonificacion_ley,
            'bonificacion_extra' => $pago->bonificacion_extra,
            'descuento_igss'     => $pago->descuento_igss,
            'descuentos_varios'  => $pago->descuentos_varios,
            'total'              => $pago->total,
            'tipo_estado_id'     => $pago->tipo_estado_id,
            'tipo_estado_nombre' => $pago->tipoEstado->tipo ?? 'N/A',
        ]);
    }

}
