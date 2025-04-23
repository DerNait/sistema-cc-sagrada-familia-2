<?php

namespace App\Exports;

use App\Models\Empleado;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmpleadosExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Obtenemos todos los empleados con relación a su usuario
        return Empleado::with('usuario')->get();
    }

    public function map($empleado): array
    {
        // Verificamos si hay un usuario asociado
        $usuario = $empleado->usuario;
        
        return [
            'id' => $empleado->id,
            'nombre' => $usuario ? $usuario->name : 'N/A',
            'apellido' => $usuario ? $usuario->apellido : 'N/A',
            'correo' => $usuario ? $usuario->email : 'N/A',
            'salario_base' => $empleado->salario_base,
            'fecha_contratacion' => $empleado->created_at->format('Y-m-d'),
            'ultimo_update' => $empleado->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID', 
            'Nombre', 
            'Apellido', 
            'Correo Electrónico',
            'Salario Base', 
            'Fecha de Contratación', 
            'Última Actualización',
        ];
    }
}