<?php

namespace App\Exports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmpleadosExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Empleado::all(['id', 'usuario_id', 'salario_base', 'created_at', 'updated_at']);
    }

    public function headings(): array
    {
        return [
            'ID', 'Usuario ID', 'Salario Base', 'Fecha de Creación', 'Fecha de Actualización',
        ];
    }
}

