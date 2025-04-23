<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmpleadosExport; // Asegúrate de importar la clase EmpleadosExport

class ExportController extends Controller
{
    public function export()
    {
        // Obtener los empleados desde la base de datos y exportarlos a un archivo Excel
        return Excel::download(new EmpleadosExport, 'empleados.xlsx');
    }
}
    