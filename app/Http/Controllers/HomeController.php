<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ModulePermission;
use App\Models\Module;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $rolId = $user->rol_id;

        $modulos = collect();

        if ($rolId === 1) {
            // Admin ve todos los módulos
            $modulos = Module::orderBy('modulo')->get(['id', 'modulo']);
        } else {
            // Los módulos permitidos para el rol del usuario
            $modulos = ModulePermission::whereHas('roles', function ($query) use ($rolId) {
                $query->where('rol_id', $rolId);
            })
            ->with('module') // cargamos módulo relacionado
            ->get()
            ->pluck('module')
            ->unique('id')
            ->sortBy('modulo')
            ->values();
        }

        $params = [
            'modulos' => $modulos,
            'usuario' => [
                'nombre' => $user->name,
            ],
        ];

        return view('component', [
            'component' => 'Home',
            'params'    => $params,
        ]);
    }
}
