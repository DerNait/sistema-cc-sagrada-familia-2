<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmpleadosExport;
use App\Exports\ProductoExport;
// use App\Exports\CalificacionesExport;
class ExportController extends Controller
{
    public function export(Request $request)
    {
      switch ($request->tipo){  
        case 1:
            return Excel::download(new EmpleadosExport, 'empleados.csv');// Obtener los empleados desde la base de datos y exportarlos a un archivo csv
        case 2:
            return Excel::download(new ProductoExport, 'productos.csv');
        // case 3:
        //     return Excel::download(new CalificacionesExport, 'calificaciones.csv');
        default:
            return response()->json(['error'=>'Tipo de exportacion no valido'], 400);
      }
    }
}