<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Estudiante;
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
}
