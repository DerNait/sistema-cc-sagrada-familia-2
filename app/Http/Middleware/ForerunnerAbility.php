<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use App\Support\Forerunner\Forerunner;

class ForerunnerAbility
{
    public function handle($request, Closure $next)
    {
        // Root siempre pasa
        if (Forerunner::isRoot()) {
            return $next($request);
        }

        $name = Route::currentRouteName(); // Ej: catalogos.roles.update
        if (!$name) {
            \Log::warning('Ruta sin nombre detectada');
            return abort(403, 'Ruta sin nombre');
        }

        $segments = explode('.', $name);

        // Rutas como 'login', 'logout', 'register', etc. se permiten
        if (count($segments) === 1) {
            \Log::info("Ruta simple permitida: $name");
            return $next($request);
        }

        $action = array_pop($segments); // Último segmento (index, create, etc.)
        $module = array_pop($segments); // Penúltimo segmento (roles, usuarios, dashboard...)

        $map = config('forerunner.map');

        // Soporte para alias personalizados (opcional)
        $actionOriginal = $action;
        $action = $map['aliases'][$module][$action] ?? $action;

        // CRUD → permiso (index → read, etc.)
        $perm = $map['crud'][$action] ?? 'read'; // ← Fallback a 'read'

        // Log de depuración
        \Log::info("Ruta: $name | Module: $module | Action: $actionOriginal (alias → $action) | Permiso evaluado: {$module}.{$perm}");

        if (!$perm || !Forerunner::allows("$module.$perm")) {
            \Log::warning("Acceso denegado para: {$module}.{$perm}");
            return abort(403);
        }

        return $next($request);
    }
}
