<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    /**
     * Dashboard del perfil del usuario.
     * GET /perfil/index -> perfil.index
     */
    public function index(Request $request)
    {
        return view('perfil.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Muestra los detalles del perfil.
     * GET /perfil -> perfil.show
     */
    public function show(Request $request)
    {
        return view('perfil.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Formulario de edición de perfil.
     * GET /perfil/editar -> perfil.edit
     */
    public function edit(Request $request)
    {
        return view('perfil.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualiza datos del usuario, su foto o su contraseña.
     * PUT /perfil/editar -> perfil.update
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // ⚙️ Validación general
        $validator = Validator::make($request->all(), [
            'name'        => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'email'       => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'foto_perfil' => ['nullable', 'image', 'max:2048'], // máx 2MB

            // 🔐 Reglas de contraseña iguales al RegisterController
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ], [
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'password.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número y un carácter especial.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        try {
            // 🖼️ Si sube nueva foto
            if ($request->hasFile('foto_perfil')) {
                if (!empty($user->foto_perfil)) {
                    Storage::disk('public')->delete($user->foto_perfil);
                }

                $path = $request->file('foto_perfil')->store('avatars', 'public');
                $data['foto_perfil'] = $path;
            }

            // 🔒 Si cambia la contraseña
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']); // evita sobreescribir con null
            }

            // 💾 Guarda todo
            $user->fill($data)->save();

            return redirect()
                ->route('perfil.index')
                ->with('success', 'Perfil actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar el perfil.');
        }
    }

    /**
     * Elimina la foto de perfil del usuario.
     * POST /perfil/foto/eliminar -> perfil.foto.destroy
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
            report($e);
            return back()->with('error', 'No se pudo eliminar la foto.');
        }
    }
}
