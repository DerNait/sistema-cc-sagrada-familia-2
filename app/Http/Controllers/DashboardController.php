<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Movimiento;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard (VIEW):
     * - Totales, becas, pagos de estudiantes
     * - Estado del personal (este mes)
     * - Notas promedio por grado
     * - Productos más vendidos
     */
    public function index(): View
    {
        // === Fechas (para pagos de estudiantes por año actual) ===
        $year = now()->year;
        $yearStart = now()->setDate($year, 1, 1)->startOfDay();
        $yearEnd   = now()->setDate($year, 12, 31)->endOfDay();

        // === Totales base ===
        $totalUsers = (int) User::count();

        // Con/Sin beca
        $agg = Estudiante::leftJoin('becas', 'estudiantes.beca_id', '=', 'becas.id')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN becas.id IS NOT NULL AND COALESCE(becas.descuento, 0) > 0 THEN 1 ELSE 0 END) AS con_beca')
            ->first();

        $totalStudents  = (int) ($agg->total ?? 0);
        $withScholar    = (int) ($agg->con_beca ?? 0);
        $withoutScholar = max(0, $totalStudents - $withScholar);

        $pctWith    = $totalStudents ? round(($withScholar * 100) / $totalStudents, 2) : 0.0;
        $pctWithout = $totalStudents ? round(($withoutScholar * 100) / $totalStudents, 2) : 0.0;

        // === Pagos de estudiantes (año actual) ===
        $paidStateIdStudents = DB::table('tipo_estados')
            ->whereRaw('LOWER(tipo) = ?', ['pagado'])
            ->value('id');

        $paidStudents = 0;
        if ($paidStateIdStudents) {
            $paidStudents = DB::table('estudiante_pagos')
                ->where('tipo_estado_id', $paidStateIdStudents)
                ->where(function ($q) use ($yearStart, $yearEnd) {
                    $q->whereDate('periodo_inicio', '<=', $yearEnd)
                      ->whereDate('periodo_fin', '>=', $yearStart);
                })
                ->distinct('estudiante_id')
                ->count('estudiante_id');
        }

        $unpaidStudents = max(0, $totalStudents - (int)$paidStudents);
        $pctPaid   = $totalStudents ? round(($paidStudents * 100) / $totalStudents, 2) : 0.0;
        $pctUnpaid = $totalStudents ? round(($unpaidStudents * 100) / $totalStudents, 2) : 0.0;

        // === Empleados ===
        $totalEmployees = (int) DB::table('empleados')->count();

        // === Estado del personal (este mes) ===
        $month   = (int) now()->month;
        $yearNow = (int) now()->year;

        $paidStateIdEmployees = DB::table('tipo_estados')
            ->whereRaw('LOWER(tipo) = ?', ['pagado'])
            ->value('id');

        $employeesPaidThisMonth = 0;
        if ($paidStateIdEmployees && $totalEmployees > 0) {
            $employeesPaidThisMonth = DB::table('pagos_empleados')
                ->where('periodo_mes', $month)
                ->where('periodo_anio', $yearNow)
                ->where('tipo_estado_id', $paidStateIdEmployees)
                ->distinct('empleado_id')
                ->count('empleado_id');
        }

        $employeesUnpaidThisMonth = max(0, $totalEmployees - $employeesPaidThisMonth);
        $pctEmployeesPaidThisMonth = $totalEmployees
            ? round(($employeesPaidThisMonth * 100) / $totalEmployees, 2)
            : 0.0;

        // === Notas promedio por grado (bar chart) ===
        $gradesAvg = DB::table('estudiantes_notas')
            ->join('seccion_estudiantes', 'estudiantes_notas.seccion_estudiante_id', '=', 'seccion_estudiantes.id')
            ->join('secciones', 'seccion_estudiantes.seccion_id', '=', 'secciones.id')
            ->join('grado', 'secciones.grado_id', '=', 'grado.id')
            ->select('grado.nombre as grade_name', DB::raw('ROUND(AVG(estudiantes_notas.nota), 2) as avg_nota'))
            ->groupBy('grado.nombre')
            ->orderBy('grado.nombre')
            ->get();

        $gradesAvgChart = $gradesAvg->map(function ($row) {
            return [
                'label' => $row->grade_name,
                'value' => (float) $row->avg_nota,
            ];
        });

        // === Productos más vendidos (Top 10) ===
        $topProducts = DB::table('movimientos')
        ->join('productos', 'movimientos.producto_id', '=', 'productos.id')
        ->join('tipo_movimientos', 'movimientos.tipo_movimiento_id', '=', 'tipo_movimientos.id')
        ->where('movimientos.tipo_movimiento_id', 1) // 1 = salida
        ->select(
            'productos.nombre as product_name',
            DB::raw('SUM(movimientos.cantidad) as total_vendido')
        )
        ->groupBy('productos.id', 'productos.nombre')
        ->orderByDesc('total_vendido')
        ->limit(10)
        ->get();



        $topProductsChart = $topProducts->map(function ($row) {
            return [
                'label' => $row->product_name,
                'value' => (int) $row->total_vendido,
            ];
        });

        // === Armamos $params para la vista ===
        $params = [
            'filters' => [
                'year' => $year,
                'month' => $month,
            ],
            'totals' => [
                'users'     => $totalUsers,
                'students'  => $totalStudents,
                'employees' => $totalEmployees,
            ],
            'scholarship' => [
                'with'        => $withScholar,
                'without'     => $withoutScholar,
                'pct_with'    => $pctWith,
                'pct_without' => $pctWithout,
            ],
            'payments' => [
                'paid_students'   => (int)$paidStudents,
                'unpaid_students' => (int)$unpaidStudents,
                'pct_paid'        => $pctPaid,
                'pct_unpaid'      => $pctUnpaid,
            ],
            'staff_pay_status' => [
                'total'    => $totalEmployees,
                'paid'     => $employeesPaidThisMonth,
                'unpaid'   => $employeesUnpaidThisMonth,
                'pct_paid' => $pctEmployeesPaidThisMonth,
            ],
            'charts' => [
                'scholarship_donut' => [
                    ['label' => 'Con beca',  'value' => $withScholar,    'percent' => $pctWith],
                    ['label' => 'Sin beca',  'value' => $withoutScholar, 'percent' => $pctWithout],
                ],
                'donut_paid_unpaid' => [
                    ['label' => 'Pagados',    'value' => (int)$paidStudents,  'percent' => $pctPaid],
                    ['label' => 'No pagados', 'value' => (int)$unpaidStudents,'percent' => $pctUnpaid],
                ],
                'progress_paid_percent' => $pctPaid,
                'grades_avg_bar'        => $gradesAvgChart,
                'staff_paid_progress'   => $pctEmployeesPaidThisMonth,
                'top_products_bar'      => $topProductsChart,
            ],
            'kpis' => [
                ['label' => 'Empleados',                  'value' => $totalEmployees],
                ['label' => 'Estudiantes',                'value' => $totalStudents],
                ['label' => 'Usuarios',                   'value' => $totalUsers],
                ['label' => 'Con beca',                   'value' => $withScholar],
                ['label' => 'Sin beca',                   'value' => $withoutScholar],
                ['label' => '% con beca',                 'value' => $pctWith, 'suffix' => '%'],
                ['label' => 'Estudiantes Pagados (año)',  'value' => $paidStudents],
                ['label' => 'Estudiantes No Pagados (año)','value' => $unpaidStudents],
                ['label' => '% Personal pagado (mes)',    'value' => $pctEmployeesPaidThisMonth, 'suffix' => '%'],
            ],
        ];

        // === Retorna la vista ===
        return view('dashboard', [
            'component' => 'Dashboard',
            'params'    => $params,
        ]);
    }
}