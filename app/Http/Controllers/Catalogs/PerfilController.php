<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    /**
     * Dashboard del perfil del usuario autenticado.
     * GET /perfil/index -> perfil.index
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->foto_perfil && !str_starts_with($user->foto_perfil, '/storage/')) {
            $user->foto_perfil = Storage::disk('public')->url($user->foto_perfil);
        }

        return view('perfil.index', compact('user'));
    }

    /**
     * Muestra el formulario de edición del perfil.
     * GET /perfil/editar -> perfil.edit
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        if ($user->foto_perfil && !str_starts_with($user->foto_perfil, '/storage/')) {
            $user->foto_perfil = Storage::disk('public')->url($user->foto_perfil);
        }

        return view('perfil.edit', compact('user'));
    }

    /**
     * Actualiza la información, contraseña o foto del perfil.
     * PUT /perfil/editar -> perfil.update
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'        => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'email'       => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'foto_perfil' => ['nullable', 'image', 'max:2048'], // 2 MB
            'password'    => [
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
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        try {
            // 🖼️ Subir nueva foto con nombre normalizado
            if ($request->hasFile('foto_perfil')) {
                $this->deleteOldPhoto($user);

                $file = $request->file('foto_perfil');
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid('avatar_') . '.' . $extension;

                // Guardar con nombre limpio
                $path = $file->storeAs('avatars', $filename, 'public');
                $data['foto_perfil'] = $path;
            }

            // 🔒 Encriptar contraseña si se actualiza
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->fill($data)->save();

            // ✅ Si la petición es AJAX → devolver JSON
            if ($request->expectsJson()) {
                $user->refresh();
                $user->foto_perfil = $user->foto_perfil
                    ? Storage::disk('public')->url($user->foto_perfil)
                    : null;

                return response()->json([
                    'message' => 'Perfil actualizado correctamente.',
                    'user'    => $user,
                ]);
            }

            // ✅ Si es desde Blade (formulario normal)
            return redirect()
                ->route('perfil.index')
                ->with('success', 'Perfil actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Error al actualizar el perfil.'], 500);
            }

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
            $this->deleteOldPhoto($user);

            $user->foto_perfil = null;
            $user->save();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Foto de perfil eliminada.']);
            }

            return back()->with('success', 'Foto de perfil eliminada.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'No se pudo eliminar la foto.'], 500);
            }

            return back()->with('error', 'No se pudo eliminar la foto.');
        }
    }

    /**
     * 🔧 Elimina la foto anterior del usuario si existe.
     */
    private function deleteOldPhoto($user): void
    {
        if (!empty($user->foto_perfil) && Storage::disk('public')->exists($user->foto_perfil)) {
            Storage::disk('public')->delete($user->foto_perfil);
        }
    }
}
