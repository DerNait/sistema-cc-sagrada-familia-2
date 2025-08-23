<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\EstudiantePago;

class CheckPaymentStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            // Buscar el último pago aprobado del usuario
            $pago = EstudiantePago::where('aprobado_id', $user->id)
                        ->orderByDesc('aprobado_en')
                        ->first();

            if ($pago) {
                $fechaPago = Carbon::parse($pago->aprobado_en);
                $diasTolerancia = 30; // puedes hacerlo dinámico después

                // Validar si ya venció
                if (Carbon::now()->diffInDays($fechaPago) > $diasTolerancia) {
                    if (!$request->is('pago*')) {
                        return redirect()->route('pago.index')
                            ->withErrors(['msg' => 'Tu suscripción ha expirado, realiza el pago.']);
                    }
                }
            } else {
                // Nunca ha pagado → solo permitir la ruta de pago
                if (!$request->is('pago*')) {
                    return redirect()->route('pago.index')
                        ->withErrors(['msg' => 'No tienes un pago registrado.']);
                }
            }
        }

        return $next($request);
    }
}