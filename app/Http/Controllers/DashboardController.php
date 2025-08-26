<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
     * Resumen para el dashboard (datos neutrales):
     * - total de usuarios
     * - total de estudiantes
     * - estudiantes con beca vs sin beca
     * - porcentajes con/sin beca
     */
    public function index(): JsonResponse
    {
        $totalUsers = (int) User::count();

        // LEFT JOIN para contar también estudiantes sin beca asociada
        // Regla: "con beca" si existe beca y su descuento > 0; lo demás es "sin beca"
        $agg = Estudiante::leftJoin('becas', 'estudiantes.beca_id', '=', 'becas.id')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN becas.id IS NOT NULL AND COALESCE(becas.descuento, 0) > 0 THEN 1 ELSE 0 END) AS con_beca')
            ->first();

        $totalStudents  = (int) ($agg->total ?? 0);
        $withScholar    = (int) ($agg->con_beca ?? 0);
        $withoutScholar = max(0, $totalStudents - $withScholar);

        $pctWith    = $totalStudents ? round(($withScholar * 100) / $totalStudents, 2) : 0.0;
        $pctWithout = $totalStudents ? round(($withoutScholar * 100) / $totalStudents, 2) : 0.0;

        return response()->json([
            'totals' => [
                'users'    => $totalUsers,
                'students' => $totalStudents,
            ],
            'scholarships' => [
                'with'      => $withScholar,
                'without'   => $withoutScholar,
                'percent'   => [
                    'with'    => $pctWith,
                    'without' => $pctWithout,
                ],
            ],
        ]);
    }
}
