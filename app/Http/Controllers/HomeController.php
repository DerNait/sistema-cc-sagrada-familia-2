<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\ModulePermission;
use App\Models\Module;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $rolId = $user->rol_id;

        $perms = ($rolId === 1)
            ? ModulePermission::with([
                'module:id,modulo',
                // cargamos la relación y el conteo de hijos
                'primaryMenu' => fn($q) => $q->select('id','module_permission_id','route','icon','parent_id','order')
                                            ->withCount('children'),
            ])->get()
            : ModulePermission::whereHas('roles', fn($q) => $q->where('rol_id', $rolId))
                ->with([
                    'module:id,modulo',
                    'primaryMenu' => fn($q) => $q->select('id','module_permission_id','route','icon','parent_id','order')
                                                ->withCount('children'),
                ])->get();

        $modulos = $perms
            ->filter(fn($mp) => $mp->module && $mp->primaryMenu)
            ->filter(fn($mp) => ($mp->primaryMenu->children_count ?? 0) === 0)
            ->filter(fn($mp) => filled($mp->primaryMenu->route) && Route::has($mp->primaryMenu->route))
            ->unique(fn($mp) => $mp->module->id)
            ->values()
            ->map(function ($mp) {
                $routeName = $mp->primaryMenu->route;
                return [
                    'id'        => $mp->module->id,
                    'modulo'    => $mp->module->modulo,
                    'icon'      => $mp->primaryMenu->icon,
                    'route_url' => route($routeName),
                ];
            });

        $incompletos = $perms->filter(fn($mp) => !$mp->module || !$mp->primaryMenu);
        if ($incompletos->isNotEmpty()) {
            \Log::warning('ModulePermissions incompletos', [
                'ids' => $incompletos->pluck('id'),
            ]);
        }

        $params = [
            'modulos' => $modulos,
            'usuario' => ['nombre' => $user->name],
        ];

        return view('component', [
            'component' => 'Home',
            'params'    => $params,
        ]);
    }

}
