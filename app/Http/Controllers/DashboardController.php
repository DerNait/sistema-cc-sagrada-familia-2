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
     * Resumen para el dashboard (JSON):
     * - total de usuarios
     * - total de estudiantes
     * - estudiantes con beca vs sin beca (descuento > 0) + porcentajes
     * - estudiantes pagados vs no pagados (año actual) + porcentajes
     * - total de empleados
     */
    public function index(): JsonResponse
    {
        // --- Año actual ---
        $year = now()->year;
        $yearStart = now()->setDate($year, 1, 1)->startOfDay();
        $yearEnd   = now()->setDate($year, 12, 31)->endOfDay();

        // --- Totales base ---
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

        // Pagados / No pagados
        $paidStateId = DB::table('tipo_estados')
            ->whereRaw('LOWER(tipo) = ?', ['pagado'])
            ->value('id');

        $paidStudents = 0;
        if ($paidStateId) {
            $paidStudents = DB::table('estudiante_pagos')
                ->where('tipo_estado_id', $paidStateId)
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

        // --- Empleados ---
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
            'scholarship' => [
                'with'         => $withScholar,
                'without'      => $withoutScholar,
                'pct_with'     => $pctWith,
                'pct_without'  => $pctWithout,
            ],
            'payments' => [
                'paid_students'   => (int)$paidStudents,
                'unpaid_students' => (int)$unpaidStudents,
                'pct_paid'        => $pctPaid,
                'pct_unpaid'      => $pctUnpaid,
            ],
            'charts' => [
                'progress_paid_percent' => $pctPaid,
                'donut_paid_unpaid' => [
                    ['label' => 'Pagados',    'value' => (int)$paidStudents],
                    ['label' => 'No pagados', 'value' => (int)$unpaidStudents],
                ],
            ],
            'kpis' => [
                ['label' => 'Empleados',   'value' => $totalEmployees],
                ['label' => 'Estudiantes', 'value' => $totalStudents],
                ['label' => 'Usuarios',    'value' => $totalUsers],
            ],
        ];

        return response()->json($payload);
    }
}
