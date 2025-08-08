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

        $name = Route::currentRouteName(); // p.ej. 'catalogos.cursos.index' o 'cursos.index'
        if (!$name) {
            \Log::warning('Ruta sin nombre detectada');
            return abort(403, 'Ruta sin nombre');
        }

        $segments = explode('.', $name);

        // Rutas de 1 solo segmento (login, logout, etc.) se permiten
        if (count($segments) === 1) {
            \Log::info("Ruta simple permitida: $name");
            return $next($request);
        }

        // Acción = último segmento
        $action = array_pop($segments);          // index | show | edit | update | ...
        // Módulo = TODO lo demás junto con puntos (soporta 'catalogos.cursos', 'admin.usuarios', etc.)
        $module = implode('.', $segments);       // 'catalogos.cursos' | 'cursos' | 'dashboard' ...

        // Lee config (tu archivo tiene 'crud' y 'aliases' en la raíz)
        $crudMap = config('forerunner.crud', []);        // index->read, edit->update, ...
        $aliases = config('forerunner.aliases', []);     // alias por módulo

        // Alias por módulo (primero intenta con módulo completo, luego con el corto)
        $shortModule = str_contains($module, '.')
            ? substr($module, strrpos($module, '.') + 1)
            : $module;

        $actionOriginal = $action;
        $action = $aliases[$module][$action]
               ?? $aliases[$shortModule][$action]
               ?? $action;

        // Acción CRUD → permiso (fallback a 'read' si no existe)
        $perm = $crudMap[$action] ?? 'read';

        \Log::info("Ruta: $name | Module: $module | Action: $actionOriginal (alias→$action) | Permiso: {$module}.{$perm}");

        if (!$perm || !Forerunner::allows("$module.$perm")) {
            \Log::warning("Acceso denegado: {$module}.{$perm}");
            return abort(403);
        }

        return $next($request);
    }
}
