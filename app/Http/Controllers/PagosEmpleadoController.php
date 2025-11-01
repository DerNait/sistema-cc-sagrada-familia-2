<?php

namespace App\Http\Controllers;

use App\Models\EstudiantePago;
use App\Models\GradoPrecio;
use App\Models\TipoPago;
use App\Models\TipoEstado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagosEmpleadoController extends Controller
{
    public function index()
    {
        return view('component', [
            'component' => 'estudiante-pago',
        ]);
    }
}