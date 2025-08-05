<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalCalificado = 90;
        $totalCurso = 100;

        // Opciones para la gráfica de Total Calificado
        $chartTotalCalificado = [
            'chart' => [
                'type' => 'pie',
                'backgroundColor' => 'transparent',
            ],
            'title' => ['text' => 'Total calificado'],
            'plotOptions' => [
                'pie' => [
                    'innerSize' => '80%',
                    'dataLabels' => ['enabled' => false]
                ]
            ],
            'tooltip' => ['enabled' => false],
            'series' => [[
                'name' => 'Calificación',
                'data' => [
                    ['name' => 'Calificado', 'y' => $totalCalificado, 'color' => '#00284B'],
                    ['name' => 'Restante', 'y' => 100 - $totalCalificado, 'color' => '#ffffff']
                ]
            ]]
        ];

        // Opciones para la gráfica de Total del curso
        $chartTotalCurso = [
            'chart' => [
                'type' => 'pie',
                'backgroundColor' => 'transparent',
            ],
            'title' => ['text' => 'Total del curso'],
            'plotOptions' => [
                'pie' => [
                    'innerSize' => '80%',
                    'dataLabels' => ['enabled' => false]
                ]
            ],
            'tooltip' => ['enabled' => false],
            'series' => [[
                'name' => 'Curso',
                'data' => [
                    ['name' => 'Completado', 'y' => 66, 'color' => '#00284B'],
                    ['name' => 'Restante', 'y' => 67 - 66, 'color' => '#ffffff']
                ]
            ]]
        ];

        $params = [
            'curso_name' => 'Matemáticas',
            'actividades' => [
                [
                    'asignacion' => ['nombre' => 'Tarea 1', 'total' => 100],
                    'fecha_inicio' => '2025-08-01',
                    'fecha_fin' => '2025-08-10',
                    'comentario' => 'Completar ejercicios',
                    'nota' => 90
                ]
            ],
            'cursos' => [],
            'total_calificado' => $chartTotalCalificado,
            'total_del_curso' => $chartTotalCurso,
            'center_labels' => [
                'total_calificado' => '90/100',
                'total_del_curso'  => '66/67'
            ]
        ];

        return view('component',[
            'component' => 'estudiante-curso',
            'params' => $params,
        ]);
    }
}
