<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Estudiante;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard (JSON):
     * - Totales: users, students, employees
     * - Becas: con/sin + % (contador + mini donut)
     * - Pagos estudiantes: pagados/no pagados + %
     */
    public function index(): JsonResponse
    {
        // --- Filtro por año (para pagos) ---
        $year = now()->year;
        $yearStart = now()->setDate($year, 1, 1)->startOfDay();
        $yearEnd   = now()->setDate($year, 12, 31)->endOfDay();

        // --- Totales base ---
        $totalUsers = (int) User::count();

        // Con/Sin beca (beca con descuento > 0)
        $agg = Estudiante::leftJoin('becas', 'estudiantes.beca_id', '=', 'becas.id')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN becas.id IS NOT NULL AND COALESCE(becas.descuento, 0) > 0 THEN 1 ELSE 0 END) AS con_beca')
            ->first();

        $totalStudents  = (int) ($agg->total ?? 0);
        $withScholar    = (int) ($agg->con_beca ?? 0);
        $withoutScholar = max(0, $totalStudents - $withScholar);

        $pctWith    = $totalStudents ? round(($withScholar * 100) / $totalStudents, 2) : 0.0;
        $pctWithout = $totalStudents ? round(($withoutScholar * 100) / $totalStudents, 2) : 0.0;

        // Pagados / No pagados (por año)
        $paidStateId = DB::table('tipo_estados')
            ->whereRaw('LOWER(tipo) = ?', ['pagado'])
            ->value('id');

        $paidStudents = 0;
        if ($paidStateId) {
            $paidStudents = DB::table('estudiante_pagos')
                ->where('tipo_estado_id', $paidStateId)
                ->where(function ($q) use ($yearStart, $yearEnd) {
                    // overlap entre [periodo_inicio, periodo_fin] y el año
                    $q->whereDate('periodo_inicio', '<=', $yearEnd)
                      ->whereDate('periodo_fin', '>=', $yearStart);
                })
                ->distinct('estudiante_id')
                ->count('estudiante_id');
        }

        $unpaidStudents = max(0, $totalStudents - (int)$paidStudents);
        $pctPaid   = $totalStudents ? round(($paidStudents * 100) / $totalStudents, 2) : 0.0;
        $pctUnpaid = $totalStudents ? round(($unpaidStudents * 100) / $totalStudents, 2) : 0.0;

        // Empleados (tabla 'empleados')
        $totalEmployees = (int) DB::table('empleados')->count();

        // --- JSON ---
        $payload = [
            'filters' => [
                'year'  => $year,
                'range' => [
                    'start' => $yearStart->toDateTimeString(),
                    'end'   => $yearEnd->toDateTimeString(),
                ],
            ],
            'totals' => [
                'users'     => $totalUsers,
                'students'  => $totalStudents,
                'employees' => $totalEmployees,
            ],
            // === Distribución de becas (contador + mini donut) ===
            'scholarship' => [
                'with'         => $withScholar,     // contador grande: con beca
                'without'      => $withoutScholar,  // contador grande: sin beca
                'pct_with'     => $pctWith,         // % con beca
                'pct_without'  => $pctWithout,      // % sin beca
            ],
            'payments' => [
                'paid_students'   => (int)$paidStudents,
                'unpaid_students' => (int)$unpaidStudents,
                'pct_paid'        => $pctPaid,
                'pct_unpaid'      => $pctUnpaid,
            ],
            'charts' => [
                // Mini donut para becas
                'scholarship_donut' => [
                    ['label' => 'Con beca',  'value' => $withScholar,    'percent' => $pctWith],
                    ['label' => 'Sin beca',  'value' => $withoutScholar, 'percent' => $pctWithout],
                ],
                // Donut pagos (ya lo tenías)
                'donut_paid_unpaid' => [
                    ['label' => 'Pagados',    'value' => (int)$paidStudents,  'percent' => $pctPaid],
                    ['label' => 'No pagados', 'value' => (int)$unpaidStudents,'percent' => $pctUnpaid],
                ],
                // Barra de progreso para pagos (si la usas)
                'progress_paid_percent' => $pctPaid,
            ],
            // KPIs grandes (tarjetas)
            'kpis' => [
                ['label' => 'Empleados',            'value' => $totalEmployees],
                ['label' => 'Estudiantes',          'value' => $totalStudents],
                ['label' => 'Usuarios',             'value' => $totalUsers],
                ['label' => 'Con beca',             'value' => $withScholar],
                ['label' => 'Sin beca',             'value' => $withoutScholar],
                ['label' => '% con beca',           'value' => $pctWith,   'suffix' => '%'],
                ['label' => 'Estudiantes Pagados',  'value' => $paidStudents],
                ['label' => 'Estudiantes No Pagados','value' => $unpaidStudents],
            ],
        ];

        return response()->json($payload);
    }
}
