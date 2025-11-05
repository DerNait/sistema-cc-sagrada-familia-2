<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grado;
use App\Models\Actividad;
use App\Models\Estudiante;
use App\Models\Seccion;
use App\Models\SeccionEstudiante;
use App\Models\EstudianteNota;
use Illuminate\Http\Request;
use App\Traits\PdfExport;
use App\Support\Forerunner\Forerunner;
class CursoController extends Controller
{
    use PdfExport;
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

        $canCreate = Forerunner::allows('admin.cursos.create');

        $params = [
            'cursos' => $cursos,
            'can_create' => $canCreate,
        ];

        return view('component', [
            'component' => 'estudiante-cursos-index',
            'params'    => $params,
        ]);
    }

    public function show(Request $request, $cursoId)
    {
        $user  = auth()->user();
        $rolId = (int) $user->rol_id;

        if ($rolId === 1 || $rolId === 4) {
            // Admin y Docente comparten la misma vista / payload
            return $this->showDocenteAdmin($request, $cursoId, $rolId);
        }

        // Resto de roles: vista de estudiante
        return $this->showEstudiante($request, $cursoId);
    }

    public function data(Request $request, int $cursoId)
    {
        $user  = auth()->user();
        $rolId = (int) $user->rol_id;

        if ($rolId === 1 || $rolId === 4) {
            // Admin / Docente
            return $this->dataDocenteAdmin($request, $cursoId, $rolId);
        }

        // Estudiante
        return $this->dataEstudiante($request, $cursoId);
    }

    /* ==========================================================
    *  ADMIN / DOCENTE
    *  - Mantiene tu lógica actual.
    *  - Acepta filtros: grado_id, seccion_id, estudiante_ids[].
    *  - Acepta filtros livianos: nombre, fecha_inicio, fecha_fin.
    *  - Devuelve shape homogéneo con el de Estudiante.
    * ========================================================== */
    protected function dataDocenteAdmin(Request $request, int $cursoId, int $rolId)
    {
        $user  = auth()->user();

        // Filtros “de selección”
        $gradoId     = (int) $request->input('grado_id');
        $seccionId   = (int) $request->input('seccion_id');
        $estIdsParam = (array) $request->input('estudiante_ids', []);

        // Filtros “livianos” de actividad
        $fNombre     = trim((string) $request->input('nombre', ''));
        $fDesde      = $request->input('fecha_inicio'); // yyyy-mm-dd
        $fHasta      = $request->input('fecha_fin');    // yyyy-mm-dd

        $gradoTable = (new Grado)->getTable();

        // Grados accesibles
        $gradosQuery = Grado::query()
            ->select("$gradoTable.id", "$gradoTable.nombre")
            ->join('grado_cursos', 'grado_cursos.grado_id', '=', "$gradoTable.id")
            ->where('grado_cursos.curso_id', $cursoId);

        if ($rolId === 4) {
            // Ojo: si $user->empleado es relación, usa ->id
            $empleadoId = optional($user->empleado)->id ?? null;
            $gradosQuery->whereExists(function ($q) use ($empleadoId, $gradoTable) {
                $q->selectRaw(1)
                ->from('secciones')
                ->whereColumn('secciones.grado_id', "$gradoTable.id")
                ->where('secciones.maestro_guia_id', $empleadoId);
            });
        }

        $grados = $gradosQuery->orderBy("$gradoTable.nombre")->get();
        abort_if($grados->isEmpty(), 404, 'No hay grados disponibles.');

        if (!$gradoId) $gradoId = (int) $grados->first()->id;

        // Secciones del grado
        $seccionesQuery = Seccion::query()
            ->select('id','seccion','maestro_guia_id','grado_id')
            ->where('grado_id', $gradoId);

        if ($rolId === 4) {
            $empleadoId = optional($user->empleado)->id ?? null;
            $seccionesQuery->where('maestro_guia_id', $empleadoId);
        }

        $secciones = $seccionesQuery->orderBy('seccion')->get();
        abort_if($secciones->isEmpty(), 404, 'No hay secciones para el grado.');

        if (!$seccionId) $seccionId = (int) $secciones->first()->id;

        // Estudiantes de la sección
        $seccion = Seccion::with(['estudiantes.usuario:id,name,apellido'])
            ->select('id')
            ->findOrFail($seccionId);

        $estudiantes = $seccion->estudiantes->map(fn($e) => [
            'id'     => $e->id,
            'nombre' => trim(($e->usuario->name ?? '') . ' ' . ($e->usuario->apellido ?? '')),
        ])->values();

        // estudiante_ids seleccionados (si vienen), default = todos
        $selectedEstudianteIds = collect($estIdsParam)->map(fn($x)=>(int)$x)->filter();
        if ($selectedEstudianteIds->isEmpty()) {
            $selectedEstudianteIds = $estudiantes->pluck('id');
        }

        // Map seccion_estudiante_id por estudiante
        $mapSeccionEst = SeccionEstudiante::where('seccion_id', $seccionId)
            ->whereIn('estudiante_id', $selectedEstudianteIds)
            ->pluck('id', 'estudiante_id'); // [estudiante_id => seccion_estudiante_id]

        // Actividades del grado-curso (con filtros livianos)
        $actividadesQuery = Actividad::query()
            ->whereHas('gradoCurso', function ($q) use ($cursoId, $gradoId) {
                $q->where('curso_id', $cursoId)->where('grado_id', $gradoId);
            })
            ->select('id','nombre','total','fecha_inicio','fecha_fin','grado_curso_id');

        if ($fNombre !== '') {
            $actividadesQuery->where('nombre', 'ILIKE', "%{$fNombre}%");
        }
        if ($fDesde) { $actividadesQuery->whereDate('fecha_inicio', '>=', $fDesde); }
        if ($fHasta) { $actividadesQuery->whereDate('fecha_fin', '<=', $fHasta); }

        $actividades = $actividadesQuery->orderBy('nombre')->get();

        $actividadIds = $actividades->pluck('id');

        // Notas
        $notas = EstudianteNota::query()
            ->whereIn('actividad_id', $actividadIds)
            ->whereIn('seccion_estudiante_id', $mapSeccionEst->values())
            ->select('id','actividad_id','seccion_estudiante_id','nota','comentario','created_at','updated_at')
            ->get()
            ->groupBy('actividad_id');

        // Payload (mismo shape que estudiante)
        $nombresPorEst = $estudiantes->keyBy('id');

        $actividadesPayload = $actividades->map(function ($act) use ($notas,$mapSeccionEst,$nombresPorEst) {
            $porActividad = $notas->get($act->id, collect())->keyBy('seccion_estudiante_id');

            $rowsNotas = $nombresPorEst->map(function ($info, $estId) use ($porActividad,$mapSeccionEst) {
                $seId = $mapSeccionEst->get($estId);
                $n    = $seId ? $porActividad->get($seId) : null;

                return [
                    'estudiante_id'         => (int) $estId,
                    'estudiante_nombre'     => $info['nombre'] ?? '',
                    'seccion_estudiante_id' => $seId,
                    'id'                    => $n->id ?? null,
                    'nota'                  => $n->nota ?? null,
                    'comentario'            => $n->comentario ?? null,
                    'has_comentario'        => filled($n?->comentario),
                ];
            })->values();

            return [
                'id'           => $act->id,
                'nombre'       => $act->nombre,
                'total'        => $act->total ?? 100,
                'fecha_inicio' => $act->fecha_inicio,
                'fecha_fin'    => $act->fecha_fin,
                'notas'        => $rowsNotas,
            ];
        })->values();

        return response()->json([
            'modo'                    => $rolId === 1 ? 'admin' : 'docente',
            'grados'                  => $grados,
            'selected_grado_id'       => $gradoId,
            'secciones'               => $secciones,
            'selected_seccion_id'     => $seccionId,
            'estudiantes'             => $estudiantes,
            'selected_estudiante_ids' => $selectedEstudianteIds->values(),
            'actividades'             => $actividadesPayload,
        ]);
    }

    /* ==========================================================
    *  ESTUDIANTE
    *  - Reusa la lógica de showEstudiante pero devuelve JSON.
    *  - Acepta filtros livianos: nombre, fecha_inicio, fecha_fin.
    *  - Mantiene el mismo shape de actividad usado en tu vista.
    * ========================================================== */
    protected function dataEstudiante(Request $request, int $cursoId)
    {
        $user = auth()->user();

        // Filtros livianos
        $fNombre = trim((string) $request->input('nombre', ''));
        $fDesde  = $request->input('fecha_inicio'); // yyyy-mm-dd
        $fHasta  = $request->input('fecha_fin');    // yyyy-mm-dd

        $estudiante = Estudiante::where('usuario_id', $user->id)->firstOrFail();
        $seccionEst = SeccionEstudiante::with('seccion.grado')
            ->where('estudiante_id', $estudiante->id)
            ->firstOrFail();

        $gradoId      = $seccionEst->seccion->grado_id;
        $seccionEstId = $seccionEst->id;

        $actividadesQuery = Actividad::whereHas('gradoCurso', function ($q) use ($cursoId, $gradoId) {
                $q->where('curso_id', $cursoId)
                ->where('grado_id', $gradoId);
            })
            ->with(['notas' => fn ($q) => $q->where('seccion_estudiante_id', $seccionEstId)])
            ->select('id', 'nombre', 'total', 'fecha_inicio', 'fecha_fin');

        if ($fNombre !== '') {
            $actividadesQuery->where('nombre', 'ILIKE', "%{$fNombre}%");
        }
        if ($fDesde) { $actividadesQuery->whereDate('fecha_inicio', '>=', $fDesde); }
        if ($fHasta) { $actividadesQuery->whereDate('fecha_fin', '<=', $fHasta); }

        $actividades = $actividadesQuery
            ->orderBy('nombre')
            ->get()
            ->map(function ($act) {
                $nota = $act->notas->first(); // puede ser null

                return [
                    'id'            => $act->id,
                    'nombre'        => $act->nombre,
                    'comentario'    => optional($nota)->comentario,
                    'nota'          => optional($nota)->nota,
                    'fecha_inicio'  => $act->fecha_inicio,
                    'fecha_fin'     => $act->fecha_fin,
                    'asignacion'    => [
                        'nombre' => $act->nombre,
                        'total'  => $act->total ?? 100,
                    ],
                ];
            })->values();

        // Para mantener shape homogéneo con el de docente/admin:
        return response()->json([
            'modo'                    => 'estudiante',
            'grados'                  => [],               // no aplica
            'selected_grado_id'       => null,             // no aplica
            'secciones'               => [],               // no aplica
            'selected_seccion_id'     => null,             // no aplica
            'estudiantes'             => [],               // no aplica
            'selected_estudiante_ids' => [],               // no aplica
            'actividades'             => $actividades,
        ]);
    }

    protected function showDocenteAdmin(Request $request, int $cursoId, int $rolId)
    {
        $user  = auth()->user();
        $curso = Curso::findOrFail($cursoId);

        // Nombre real de la tabla de Grado (evita hardcodear 'grado'/'grados')
        $gradoTable = (new Grado)->getTable(); // te devolverá 'grado'

        // 1) Grados accesibles
        $gradosQuery = Grado::query()
            ->select("$gradoTable.id", "$gradoTable.nombre")
            ->join('grado_cursos', 'grado_cursos.grado_id', '=', "$gradoTable.id")
            ->where('grado_cursos.curso_id', $cursoId);

        if ($rolId === 4) { // DOCENTE: solo grados donde tenga secciones asignadas
            $empleadoId = $user->empleado ?? null;
            $gradosQuery->whereExists(function ($q) use ($empleadoId, $gradoTable) {
                $q->selectRaw(1)
                ->from('secciones')
                ->whereColumn('secciones.grado_id', "$gradoTable.id")
                ->where('secciones.maestro_guia_id', $empleadoId);
            });
        }

        $grados = $gradosQuery->orderBy("$gradoTable.nombre")->get();
        abort_if($grados->isEmpty(), 404, 'No hay grados disponibles para este curso.');

        $gradoId = (int)($request->input('grado_id') ?: $grados->first()->id);

        // 2) Secciones del grado
        $seccionesQuery = Seccion::query()
            ->select('id', 'seccion', 'maestro_guia_id', 'grado_id')
            ->where('grado_id', $gradoId);

        if ($rolId === 4) { // DOCENTE: solo sus secciones
            $empleadoId = $user->empleado ?? null;
            $seccionesQuery->where('maestro_guia_id', $empleadoId);
        }

        $secciones = $seccionesQuery->orderBy('seccion')->get();
        abort_if($secciones->isEmpty(), 404, 'No hay secciones para el grado seleccionado.');

        $seccionId = (int)($request->input('seccion_id') ?: $secciones->first()->id);

        // 3) Estudiantes de la sección
        $seccion = Seccion::with(['estudiantes.usuario:id,name,apellido'])
            ->select('id')
            ->findOrFail($seccionId);

        $estudiantes = $seccion->estudiantes->map(fn($e) => [
            'id'     => $e->id,
            'nombre' => trim(($e->usuario->name ?? '') . ' ' . ($e->usuario->apellido ?? '')),
        ])->values();

        $selectedEstudianteIds = collect((array) $request->input('estudiante_ids'))
            ->map(fn($x) => (int)$x)
            ->filter()
            ->values();

        if ($selectedEstudianteIds->isEmpty()) {
            $selectedEstudianteIds = $estudiantes->pluck('id');
        }

        // Mapa seccion_estudiante_id por estudiante_id
        $mapSeccionEst = SeccionEstudiante::where('seccion_id', $seccionId)
            ->whereIn('estudiante_id', $selectedEstudianteIds)
            ->pluck('id', 'estudiante_id'); // [estudiante_id => seccion_estudiante_id]

        // 4) Actividades del grado-curso
        $actividades = Actividad::query()
            ->whereHas('gradoCurso', function ($q) use ($cursoId, $gradoId) {
                $q->where('curso_id', $cursoId)
                ->where('grado_id', $gradoId);
            })
            ->select('id', 'nombre', 'total', 'fecha_inicio', 'fecha_fin', 'grado_curso_id')
            ->orderBy('nombre')
            ->get();

        $actividadIds = $actividades->pluck('id');

        // 5) Notas de esos estudiantes en esas actividades
        $notas = EstudianteNota::query()
            ->whereIn('actividad_id', $actividadIds)
            ->whereIn('seccion_estudiante_id', $mapSeccionEst->values())
            ->select('id', 'actividad_id', 'seccion_estudiante_id', 'nota', 'comentario', 'created_at', 'updated_at')
            ->get()
            ->groupBy('actividad_id');

        $nombresPorEst = $estudiantes->keyBy('id');

        $actividadesPayload = $actividades->map(function ($act) use ($notas, $mapSeccionEst, $nombresPorEst) {
            $porActividad = $notas->get($act->id, collect())->keyBy('seccion_estudiante_id');

            $rowsNotas = $nombresPorEst->map(function ($info, $estId) use ($porActividad, $mapSeccionEst) {
                $seId = $mapSeccionEst->get($estId);
                $n    = $seId ? $porActividad->get($seId) : null;

                return [
                    'estudiante_id'          => (int) $estId,
                    'estudiante_nombre'      => $info['nombre'] ?? '',
                    'seccion_estudiante_id'  => $seId,
                    'id'                     => $n->id ?? null,
                    'nota'                   => $n->nota ?? null,
                    'comentario'             => $n->comentario ?? null,
                    'has_comentario'         => filled($n?->comentario),
                ];
            })->values();

            return [
                'id'           => $act->id,
                'nombre'       => $act->nombre,
                'total'        => $act->total ?? 100,
                'fecha_inicio' => $act->fecha_inicio,
                'fecha_fin'    => $act->fecha_fin,
                'notas'        => $rowsNotas,
            ];
        })->values();

        $canEdit = Forerunner::allows('admin.cursos.edit');
        $canActivity = Forerunner::allows('admin.actividades.create');

        $params = [
            'modo'                    => $rolId === 1 ? 'admin' : 'docente',
            'curso'                   => ['id' => $curso->id, 'nombre' => $curso->nombre],
            'grados'                  => $grados,
            'selected_grado_id'       => $gradoId,
            'secciones'               => $secciones,
            'selected_seccion_id'     => $seccionId,
            'estudiantes'             => $estudiantes,
            'selected_estudiante_ids' => $selectedEstudianteIds,
            'actividades'             => $actividadesPayload,
            'can_edit'                => $canEdit,
            'can_create_activity'     => $canActivity,
        ];

        return view('component', [
            'component' => 'docente-curso',
            'params'    => $params,
        ]);
    }

    protected function showEstudiante(Request $request, int $cursoId) 
    {
        $user  = auth()->user();
        $curso = Curso::findOrFail($cursoId);

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
                    ->select('id', 'nombre', 'total', 'fecha_inicio', 'fecha_fin')
                    ->orderBy('nombre')
                    ->get()
                    ->map(function ($act) {
                        $nota = $act->notas->first();          // puede ser null

                        return [
                            'id'            => $act->id,
                            'nombre'        => $act->nombre,
                            'comentario'    => optional($nota)->comentario,
                            'nota'          => optional($nota)->nota,
                            // ← dejamos los strings tal cual; tu Vue ya los muestra
                            'fecha_inicio'  => $act->fecha_inicio,
                            'fecha_fin'     => $act->fecha_fin,
                            'asignacion'    => [
                                'nombre' => $act->nombre,
                                'total'  => $act->total ?? 100,   // si llega null, usa 100
                            ],
                        ];
                    });

        /* --------- Métricas para los charts --------- */
        $totalObtenido = $actividades->sum('nota');

        // Solo actividades que tienen nota (calificadas)
        $actividadesCalificadas = $actividades->filter(fn($a) => !is_null($a['nota']));

        // Suma total solo de actividades calificadas
        $totalPosible = $actividadesCalificadas->sum(fn($a) => $a['asignacion']['total']);

        $porcCompletado = $totalPosible ? round(($totalObtenido / $totalPosible) * 100) : 0;

        /* --------- Configuración Highcharts --------- */
        $chartTotalCalificado = [
            'chart'        => ['type' => 'pie', 'backgroundColor' => 'transparent'],
            'title'        => ['text' => 'Total calificado'],
            'plotOptions'  => ['pie' => ['innerSize' => '80%', 'dataLabels' => ['enabled' => false]]],
            'tooltip'      => ['enabled' => false],
            'series'       => [[
                'data' => [
                    ['name' => 'Calificado', 'y' => $totalObtenido,                   'color' => '#00284B'],
                    ['name' => 'Restante',   'y' => $totalPosible - $totalObtenido,   'color' => '#ffffff'],
                ],
            ]],
        ];

        $chartTotalCurso = [
            'chart'        => ['type' => 'pie', 'backgroundColor' => 'transparent'],
            'title'        => ['text' => 'Promedio del curso'],
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

public function exportCalificaciones(int $cursoId)
{
    $calificaciones = EstudianteNota::with([
            'actividad:id,nombre',
            'seccionEstudiante.estudiante.usuario:id,name,apellido'
        ])
        ->whereHas('actividad.gradoCurso', function ($q) use ($cursoId) {
            $q->where('curso_id', $cursoId);
        })
        ->get()
        ->map(function ($nota) {
            return [
                'Estudiante' => $nota->seccionEstudiante->estudiante->usuario->name
                    . ' ' . $nota->seccionEstudiante->estudiante->usuario->apellido,
                'Actividad'  => $nota->actividad->nombre,
                'Nota'       => $nota->nota,
                'Comentario' => $nota->comentario,
            ];
        });

    return Excel::download(
        new \Maatwebsite\Excel\Collections\SheetCollection([
            'Calificaciones' => collect($calificaciones)
        ]),
        'calificaciones.csv',
        \Maatwebsite\Excel\Excel::CSV 
    );
}

    /**
     * Exporta PDF de calificaciones del curso usando la vista Blade.
     * Requiere barryvdh/laravel-dompdf o similar.
     */
    public function exportPdfCalificaciones(Curso $curso)
    {
        $cursoId = $curso->id;

        // Obtener todas las actividades del curso (ordenadas)
        $actividades = Actividad::whereHas('gradoCurso', function ($q) use ($cursoId) {
                $q->where('curso_id', $cursoId);
            })
            ->select('id', 'nombre', 'total', 'grado_curso_id')
            ->orderBy('nombre')
            ->get();

        // Obtener todos los estudiantes que tienen notas en este curso
        $estudiantesIds = EstudianteNota::whereIn('actividad_id', $actividades->pluck('id'))
            ->distinct()
            ->pluck('seccion_estudiante_id');

        $estudiantes = SeccionEstudiante::with('estudiante.usuario:id,name,apellido')
            ->whereIn('id', $estudiantesIds)
            ->get()
            ->map(function($se) {
                $usuario = $se->estudiante->usuario ?? null;
                return [
                    'id' => $se->estudiante_id,
                    'nombre' => trim(($usuario->name ?? '') . ' ' . ($usuario->apellido ?? '')),
                ];
            })
            ->sortBy('nombre')
            ->values();

        // Construir matriz de notas [estudiante_id][actividad_id] => nota
        $seccionEstudianteMap = SeccionEstudiante::whereIn('id', $estudiantesIds)
            ->pluck('id', 'estudiante_id');

        $notasCollection = EstudianteNota::whereIn('actividad_id', $actividades->pluck('id'))
            ->whereIn('seccion_estudiante_id', $seccionEstudianteMap->values())
            ->select('actividad_id', 'seccion_estudiante_id', 'nota')
            ->get();

        $notas = [];
        foreach($estudiantes as $est) {
            $notas[$est['id']] = [];
            foreach($actividades as $act) {
                $seId = $seccionEstudianteMap->get($est['id']);
                $nota = $notasCollection->first(function($n) use ($act, $seId) {
                    return $n->actividad_id == $act->id && $n->seccion_estudiante_id == $seId;
                });
                $notas[$est['id']][$act->id] = $nota ? $nota->nota : null;
            }
        }

        $data = [
            'curso' => $curso,
            'actividades' => $actividades,
            'estudiantes' => $estudiantes,
            'notas' => $notas,
        ];

        // Si tienes DomPDF instalado:
        // $pdf = \PDF::loadView('reporte-calificaciones-cursos', $data);
        // return $pdf->download("calificaciones-{$curso->nombre}.pdf");

        // Mientras tanto, devuelve la vista (para preview):
        return view('reporte-calificaciones-cursos', $data);
    }
}
