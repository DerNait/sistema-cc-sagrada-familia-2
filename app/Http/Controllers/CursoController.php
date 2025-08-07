<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Actividad;
use App\Models\Estudiante;
use App\Models\SeccionEstudiante;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $rolId  = $user->rol_id;

        // ---------- ADMIN (rol_id = 1) ----------
        if ($rolId === 1) {
            $cursos = Curso::select('id', 'nombre', 'imagen', 'icono', 'color')
                        ->orderBy('nombre')
                        ->get();

        // ---------- DOCENTE (rol_id = 4) ----------
        } elseif ($rolId === 4) {
            $cursos = $user->cursosAsignados()
                        ->select('cursos.id', 'cursos.nombre', 'cursos.imagen', 'cursos.icono', 'cursos.color')
                        ->orderBy('cursos.nombre')
                        ->get();

        // ---------- ESTUDIANTE ----------
        } else {
            $cursos = Curso::whereHas('grados.secciones.estudiantes', function ($q) use ($user) {
                                $q->where('usuario_id', $user->id);
                            })
                            ->select('cursos.id', 'cursos.nombre', 'cursos.imagen', 'cursos.icono', 'cursos.color')
                            ->distinct()
                            ->orderBy('cursos.nombre')
                            ->get();
        }

        $params = [
            'cursos' => $cursos,
        ];

        return view('component', [
            'component' => 'estudiante-cursos-index',
            'params'    => $params,
        ]);
    }

    public function show(Request $request, $cursoId)
    {
        $user = auth()->user();
        $rolId = $user->rol_id;

        $curso = Curso::where('id', $cursoId)->firstOrFail();

        // Vista para ADMINISTRADOR (rol_id = 1)
        if ($rolId === 1) {
            // TODO: Puedes agregar aquí más lógica si el admin necesita más datos
            $params = [
                'curso' => $curso,
                'modo' => 'admin',
            ];

            return view('component', [
                'component' => 'admin-curso-detalle',
                'params'    => $params,
            ]);
        }

        // Vista para DOCENTE (rol_id = 4)
        if ($rolId === 4) {
            $actividades = Actividad::whereHas('gradoCurso', function ($q) use ($cursoId) {
                    $q->where('curso_id', $cursoId);
                })
                ->with('notas') // Aquí el docente puede ver todas las notas de todos los estudiantes
                ->select('id', 'nombre')
                ->get();

            $params = [
                'curso' => $curso,
                'actividades' => $actividades,
                'modo' => 'docente',
            ];

            return view('component', [
                'component' => 'docente-curso-detalle',
                'params'    => $params,
            ]);
        }

        // ---------------------------------- ESTUDIANTE ----------------------------------
        $estudiante  = Estudiante::where('usuario_id', $user->id)->firstOrFail();
        $seccionEst  = SeccionEstudiante::with('seccion.grado')
                        ->where('estudiante_id', $estudiante->id)
                        ->firstOrFail();

        $gradoId      = $seccionEst->seccion->grado_id;
        $seccionEstId = $seccionEst->id;

        /* -------- Actividades del curso y grado del alumno -------- */
        $actividades = Actividad::whereHas('gradoCurso', function ($q) use ($cursoId, $gradoId) {
                                $q->where('curso_id', $cursoId)
                                ->where('grado_id', $gradoId);
                            })
                            ->with(['notas' => fn ($q) =>
                                $q->where('seccion_estudiante_id', $seccionEstId)
                            ])
                            ->select('id', 'nombre')          // ya no pedimos total/fechas
                            ->orderBy('nombre')
                            ->get()
                            ->map(function ($act) {
                                $nota = $act->notas->first();      // puede ser null

                                return [
                                    'id'         => $act->id,
                                    'nombre'     => $act->nombre,
                                    'comentario' => optional($nota)->comentario,  // ← viene de la nota
                                    'nota'       => optional($nota)->nota,
                                    // objeto usado por los slots Vue
                                    'asignacion' => [
                                        'nombre' => $act->nombre,
                                        'total'  => 100,            // fijo a 100 hasta que migres
                                    ],
                                ];
                            });

        /* --------- Datos para los gráficos circulares --------- */
        $totalPosible   = $actividades->count() * 100;           // 100 por actividad
        $totalObtenido  = $actividades->sum('nota');
        $porcCompletado = $totalPosible ? round(($totalObtenido / $totalPosible) * 100) : 0;

        /* --------- Configuración Highcharts --------- */
        $chartTotalCalificado = [
            'chart'        => ['type' => 'pie', 'backgroundColor' => 'transparent'],
            'title'        => ['text' => 'Total calificado'],
            'plotOptions'  => ['pie' => ['innerSize' => '80%', 'dataLabels' => ['enabled' => false]]],
            'tooltip'      => ['enabled' => false],
            'series'       => [[
                'data' => [
                    ['name' => 'Calificado', 'y' => $totalObtenido,               'color' => '#00284B'],
                    ['name' => 'Restante',   'y' => $totalPosible - $totalObtenido,'color' => '#ffffff'],
                ],
            ]],
        ];

        $chartTotalCurso = [
            'chart'        => ['type' => 'pie', 'backgroundColor' => 'transparent'],
            'title'        => ['text' => 'Avance del curso'],
            'plotOptions'  => ['pie' => ['innerSize' => '80%', 'dataLabels' => ['enabled' => false]]],
            'tooltip'      => ['enabled' => false],
            'series'       => [[
                'data' => [
                    ['name' => 'Completado', 'y' => $porcCompletado,       'color' => '#00284B'],
                    ['name' => 'Restante',   'y' => 100 - $porcCompletado, 'color' => '#ffffff'],
                ],
            ]],
        ];

        /* ------------ payload final para el componente Vue ------------ */
        return view('component', [
            'component' => 'estudiante-curso',
            'params'    => [
                'curso_name'       => $curso->nombre,
                'actividades'      => $actividades,
                'total_calificado' => $chartTotalCalificado,
                'total_del_curso'  => $chartTotalCurso,
                'center_labels'    => [
                    'total_calificado' => "{$totalObtenido}/{$totalPosible}",
                    'total_del_curso'  => "{$porcCompletado}%",
                ],
            ],
        ]);
    }
}
