<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    // Muestra el formulario de perfil
    public function index(Request $request)
    {
        return view('perfil.index', [
            'user' => $request->user(),
        ]);
    }

 
    public function update(Request $request)
    {
        $user = $request->user();

        
        $data = $request->validate([
            'nombre'       => ['nullable','string','max:255'],
            'apellido'     => ['nullable','string','max:255'],
            'email'        => ['nullable','email','max:255'], 
            'foto_perfil'  => ['nullable','image','max:2048'], 
        ]);

        try {
            if ($request->hasFile('foto_perfil')) {
              
                if ($user->foto_perfil) {
                    Storage::disk('public')->delete($user->foto_perfil);
                }

               
                $path = $request->file('foto_perfil')->store('avatars', 'public');
                $data['foto_perfil'] = $path;
            }

            $user->fill($data)->save();

            return back()->with('success', 'Perfil actualizado correctamente.');
        } catch (\Throwable $e) {
           
            return back()->withInput()->with('error', 'No se pudo actualizar el perfil.');
        }
    }
}
