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

}
