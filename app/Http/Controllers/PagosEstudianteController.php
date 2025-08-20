<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\EstudiantePago;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagosEstudianteController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        // Solo estudiantes pueden entrar aquí
        if ($usuario->rol_id != 5) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo estudiantes pueden consultar sus pagos.'
            ], 403);
        }

        // Buscar al estudiante
        $estudiante = Estudiante::where('usuario_id', $usuario->id)->firstOrFail();

        // Traer pagos con relaciones
        $pagos = EstudiantePago::with(['gradoPrecio', 'tipoPago', 'tipoEstado'])
            ->where('estudiante_id', $estudiante->id)
            ->latest()
            ->get()
            ->map(function ($pago) {
                $inicio = Carbon::parse($pago->periodo_inicio);
                $fin = Carbon::parse($pago->periodo_fin);
                $hoy = Carbon::today();

                if ($hoy->between($inicio, $fin)) {
                    $pago->estado_vigencia = 'vigente';
                } elseif ($hoy->greaterThan($fin)) {
                    $pago->estado_vigencia = 'vencido';
                } else {
                    $pago->estado_vigencia = 'pendiente';
                }

                return $pago;
            });

        return response()->json([
            'success' => true,
            'rol' => 'estudiante',
            'estudiante_id' => $estudiante->id,
            'pagos' => $pagos
        ]);
    }
}
