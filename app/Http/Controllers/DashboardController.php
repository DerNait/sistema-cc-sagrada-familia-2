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
        // Cambia a 'auth:sanctum' si lo consumirás vía /api con SPA
        $this->middleware('auth');
    }

    /**
     * Resumen para el dashboard (JSON):
     * - total de usuarios
     * - total de estudiantes
     * - estudiantes con beca vs sin beca (descuento > 0)
     * - porcentajes con/sin beca
     * - estudiantes pagados vs no pagados (año actual)
     * - porcentajes pagados/no pagados
     * - estructuras listas para progress bar y donut
     */
    public function index(): JsonResponse
    {
        // --- Año actual como filtro por defecto ---
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

        // --- Pagados / No pagados ---
        // Buscamos el id del estado "Pagado" en tipo_estados.tipo
        $paidStateId = DB::table('tipo_estados')
            ->whereRaw('LOWER(tipo) = ?', ['pagado'])
            ->value('id');

        // Estudiantes con al menos un registro "Pagado" que se solape con el año actual
        $paidStudents = 0;
        if ($paidStateId) {
            $paidStudents = DB::table('estudiante_pagos')
                ->where('tipo_estado_id', $paidStateId)
                ->where(function ($q) use ($yearStart, $yearEnd) {
                    // overlap entre [periodo_inicio, periodo_fin] y [yearStart, yearEnd]
                    $q->whereDate('periodo_inicio', '<=', $yearEnd)
                      ->whereDate('periodo_fin', '>=', $yearStart);
                })
                ->distinct('estudiante_id')
                ->count('estudiante_id');
        }

        $unpaidStudents = max(0, $totalStudents - (int)$paidStudents);
        $pctPaid   = $totalStudents ? round(($paidStudents * 100) / $totalStudents, 2) : 0.0;
        $pctUnpaid = $totalStudents ? round(($unpaidStudents * 100) / $totalStudents, 2) : 0.0;

        // --- JSON listo para frontend (progress + donut) ---
        $payload = [
            'filters' => [
                'year'  => $year,
                'range' => [
                    'start' => $yearStart->toDateTimeString(),
                    'end'   => $yearEnd->toDateTimeString(),
                ],
            ],
            'totals' => [
                'users'    => $totalUsers,
                'students' => $totalStudents,
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
                // Barra de progreso: 0..100
                'progress_paid_percent' => $pctPaid,
                // Donut
                'donut_paid_unpaid' => [
                    ['label' => 'Pagados',    'value' => (int)$paidStudents],
                    ['label' => 'No pagados', 'value' => (int)$unpaidStudents],
                ],
            ],
        ];

        return response()->json($payload);
    }
}
