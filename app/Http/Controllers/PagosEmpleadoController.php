<?php

namespace App\Http\Controllers;

use App\Models\PagosEmpleado;
use App\Models\TipoEstado;
use Illuminate\Http\Request;

class PagosEmpleadoController extends Controller
{
    public function index()
    {
        // 1️⃣ Obtener estados disponibles
        $tiposEstado = TipoEstado::select('id', 'tipo')->orderBy('tipo')->get();

        // 2️⃣ Obtener los pagos con relaciones
        $pagos = PagosEmpleado::with(['empleado.user', 'tipoEstado'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pago) {
                $empleado = $pago->empleado;
                $usuario = $empleado->user ?? null;

                return [
                    'id' => $pago->id,
                    'nombre' => $usuario->nombre ?? 'N/A',
                    'apellido' => $usuario->apellido ?? 'N/A',
                    'correo' => $usuario->email ?? 'N/A',
                    'fecha_ingreso' => optional($pago->fecha_ingreso)->format('Y-m-d'),
                    'salario_base' => $pago->salario_base,
                    'bonificacion_ley' => $pago->bonificacion_ley,
                    'bonificacion_extra' => $pago->bonificacion_extra,
                    'descuento_igss' => $pago->descuento_igss,
                    'descuentos_varios' => $pago->descuentos_varios,
                    'total' => $pago->total,
                    'tipo_estado_id' => $pago->tipo_estado_id,
                    'tipo_estado_nombre' => $pago->tipoEstado->tipo ?? 'N/A',
                ];
            });

        // 3️⃣ Enviar datos al componente Vue
        $params = [
            'pagos' => $pagos,
            'tipos_estado' => $tiposEstado
        ];

        return view('component', [
            'component' => 'admin-pago-empleado',
            'params' => $params,
        ]);
    }
}
