<?php

namespace App\Exports;

use App\Models\EstudianteNota;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CalificacionesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return EstudianteNota::with(['actividad', 'seccionEstudiante.estudiante.usuario'])
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
    }

    public function headings(): array
    {
        return ['Estudiante', 'Actividad', 'Nota', 'Comentario'];
    }
}
