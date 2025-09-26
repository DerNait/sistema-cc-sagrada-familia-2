<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    /**
     * Vista de perfil (lectura): GET /perfil  -> perfil.show
     */
    public function show(Request $request)
    {
        return view('perfil.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Formulario de edición: GET /perfil/editar -> perfil.edit
     */
    public function edit(Request $request)
    {
        return view('perfil.index', [ // usa la vista de edición que ya tenías
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualiza datos/foto: POST /perfil/editar -> perfil.update
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            // Ajusta estos nombres a tu esquema real
            'name'        => ['nullable','string','max:255'],
            'nombre'      => ['nullable','string','max:255'],
            'apellido'    => ['nullable','string','max:255'],
            'email'       => ['nullable','email','max:255'],
            'foto_perfil' => ['nullable','image','max:2048'], // 2MB
        ]);

        try {
            // Si suben una nueva foto
            if ($request->hasFile('foto_perfil')) {
                // Elimina anterior si existe
                if (!empty($user->foto_perfil)) {
                    Storage::disk('public')->delete($user->foto_perfil);
                }

                // Guarda nueva en storage/app/public/avatars
                $path = $request->file('foto_perfil')->store('avatars', 'public');
                $data['foto_perfil'] = $path;
            }

            // Si usas 'nombre' en BD pero tu UI manda 'name' o viceversa,
            // sincroniza uno con otro para no perder consistencia:
            if (isset($data['nombre']) && !isset($data['name'])) {
                $data['name'] = $data['nombre'];
            }

            $user->fill($data)->save();

            return back()->with('success', 'Perfil actualizado correctamente.');
        } catch (\Throwable $e) {
            // \Log::error($e->getMessage());
            return back()->withInput()->with('error', 'No se pudo actualizar el perfil.');
        }
    }

    /**
     * Elimina la foto: POST /perfil/foto/eliminar -> perfil.foto.destroy
     */
    public function destroyPhoto(Request $request)
    {
        $user = $request->user();

        try {
            if (!empty($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
                $user->foto_perfil = null;
                $user->save();
            }
            return back()->with('success', 'Foto de perfil eliminada.');
        } catch (\Throwable $e) {
            // \Log::error($e->getMessage());
            return back()->with('error', 'No se pudo eliminar la foto.');
        }
    }
}
