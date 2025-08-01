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

        // Vista para ESTUDIANTE (rol_id = otro)
        $estudiante = Estudiante::where('usuario_id', $user->id)->firstOrFail();
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
                'id'     => $actividad->id,
                'nombre' => $actividad->nombre,
                'nota'   => optional($actividad->notas->first())->nota,
            ];
        });

        $params = [
            'curso'      => $curso,
            'actividades'=> $actividadesConNotas,
            'modo'       => 'estudiante',
        ];

        return view('component', [
            'component' => 'estudiante-curso-detalle',
            'params'    => $params,
        ]);
    }
}
