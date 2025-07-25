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
        $user  = auth()->user();
        $rolId = $user->rol_id;

        $cursos = collect();

        if ($rolId === 1) {
            $cursos = Curso::select('id', 'nombre')
                ->orderBy('nombre')
                ->get();
        } elseif ($rolId === 4) {
            $cursos = $user->cursosAsignados()
                ->select('cursos.id', 'cursos.nombre')
                ->orderBy('cursos.nombre')
                ->get();
        } else {
            $cursos = Curso::whereHas('grado.secciones.estudiantes', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->select('id', 'nombre')
                ->orderBy('nombre')
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
        
        $estudiante = Estudiante::where('user_id', $user->id)->firstOrFail();
        $curso = Curso::where('id', $cursoId)->firstOrFail();

        $seccionEstudiante = SeccionEstudiante::where('estudiante_id', $estudiante->id)->first();

        if (!$seccionEstudiante) {
            abort(404, 'No se encontró relación de sección del estudiante.');
        }

        $actividades = Actividad::whereHas('gradoCurso', function ($q) use ($cursoId) {
                $q->where('curso_id', $cursoId);
            })
            ->with(['notas' => function ($q) use ($seccionEstudiante) {
                $q->where('seccion_estudiante_id', $seccionEstudiante->id);
            }])
            ->select('id', 'nombre')
            ->get();

        $actividadesConNotas = $actividades->map(function ($actividad) {
            return [
                'id'       => $actividad->id,
                'nombre'   => $actividad->nombre,
                'nota'     => optional($actividad->notas->first())->nota,
            ];
        });

        $params = [
            'curso'      => $curso,
            'actividades'=> $actividadesConNotas,
        ];

        return view('component', [
            'component' => 'estudiante-curso-detalle',
            'params'    => $params,
        ]);
    }
}
