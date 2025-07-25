<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Auth;

class CursosEstudianteController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $estudiante = Estudiante::where('usuario_id', $usuario->id)->firstOrFail();

        $cursos = $estudiante->secciones()
            ->with('cursos') 
            ->get()
            ->pluck('cursos')
            ->flatten(); 

        return view('estudiante.cursos', compact('cursos'));
    }
}