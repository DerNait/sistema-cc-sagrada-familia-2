<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empleado;
use App\Models\PagosEmpleado;
use Illuminate\Support\Facades\DB;

class EmpleadosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function planilla()
    {
        $periodo = PagosEmpleado::select(DB::raw('MAX(periodo_anio) as anio'), DB::raw('MAX(periodo_mes) as mes'))->first();

        if (!$periodo || !$periodo->anio || !$periodo->mes) {
            abort(404, 'No hay registros de pagos para generar la planilla.');
        }

        $empleados = Empleado::with(['usuario', 'role', 'pagos' => function ($q) use ($periodo) {
            $q->where('periodo_anio', $periodo->anio)
            ->where('periodo_mes', $periodo->mes)
            ->with('ajustes.tipo');
        }])->get();

        $datosPlanilla = $empleados->map(function ($empleado) {
            $pago = $empleado->pagos->first();

            if (!$pago) return null;

            $ajustesPositivos = $pago->ajustes->filter(function ($ajuste) {
                $nombre = strtolower($ajuste->tipo->ajuste);
                return str_contains($nombre, 'bono') ||
                    str_contains($nombre, 'incentivo') ||
                    str_contains($nombre, 'extra') ||
                    str_contains($nombre, 'premio') ||
                    str_contains($nombre, 'productividad') ||
                    str_contains($nombre, 'asistencia');
            })->sum('monto');

            $ajustesNegativos = $pago->ajustes->filter(function ($ajuste) {
                $nombre = strtolower($ajuste->tipo->ajuste);
                return str_contains($nombre, 'descuento') ||
                    str_contains($nombre, 'sanción') ||
                    str_contains($nombre, 'deducción') ||
                    str_contains($nombre, 'prestamo') ||
                    str_contains($nombre, 'anticipo');
            })->sum('monto');

            return [
                'name'           => $empleado->usuario->name,
                'dpi'            => $empleado->dpi,
                'cargo'          => $empleado->role?->nombre,
                'cuenta'         => $empleado->numero_cuenta,
                'banco'          => $empleado->banco,
                'fecha_ingreso'  => $empleado->fecha_ingreso,
                'salario_base'   => $pago->monto_base,
                'bonificaciones' => $ajustesPositivos,
                'descuentos'     => $ajustesNegativos,
                'total'          => $pago->monto_total,
            ];
        })->filter();

        $pdf = Pdf::loadView('empleados.planilla', [
            'datosPlanilla' => $datosPlanilla,
            'periodo' => $periodo,
        ]);

        return $pdf->download("planilla_salarios_{$periodo->anio}_{$periodo->mes}.pdf");
    }
}
