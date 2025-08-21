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

        if ($usuario->rol_id == 1) {
            $pagos = EstudiantePago::with(['estudiante.usuario'])
                ->get()
                ->map(function ($pago) {
                    return [
                        'id' => $pago->estudiante->id,
                        'nombre' => $pago->estudiante->usuario->name,
                        'apellido' => $pago->estudiante->usuario->apellido,
                        'correo' => $pago->estudiante->usuario->email,
                        'meses_pagados' => $pago->meses_pagados,
                        'fecha_registro' => $pago->estudiante->usuario->fecha_registro,
                        'fecha_nacimiento' => $pago->estudiante->usuario->fecha_nacimiento,
                    ];
                });

            return response()->json([
                'success' => true,
                'rol' => 'admin',
                'pagos' => $pagos
            ]);
        }

        if ($usuario->rol_id == 5) {
            $estudiante = Estudiante::where('usuario_id', $usuario->id)->firstOrFail();

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

        return response()->json([
            'success' => false,
            'message' => 'Acceso denegado. Solo estudiantes y administradores pueden consultar pagos.'
        ], 403);
    }
}
