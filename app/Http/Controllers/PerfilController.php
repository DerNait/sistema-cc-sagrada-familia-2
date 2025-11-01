<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $user->loadMissing(['role']);

        if ($user->foto_perfil && !str_starts_with($user->foto_perfil, '/storage/')) {
            $user->foto_perfil = Storage::disk('public')->url($user->foto_perfil);
        }

        $params = ['user' => $user];

        return view('component', [
            'component' => 'perfil',
            'params'    => $params,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'password'           => ['nullable', 'string', 'min:8', 'confirmed'],
            'foto_perfil_path'   => ['nullable', 'string'],
            'remove_photo'       => ['nullable', 'boolean'],
        ]);

        // 1) Cambiar contraseña (si viene)
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // 2) Foto de perfil
        if (!empty($data['remove_photo']) && $data['remove_photo'] == true) {
            $this->safeDeleteCurrentPhoto($user);
            $user->foto_perfil = null;
        } elseif (!empty($data['foto_perfil_path'])) {
            $path = ltrim($data['foto_perfil_path'], '/');

            if (str_contains($path, '..')) {
                return response()->json(['message' => 'Ruta de imagen inválida.'], 422);
            }
            if (!str_starts_with($path, 'uploads/')) {
                return response()->json(['message' => 'Ruta de imagen no permitida.'], 403);
            }

            $this->safeDeleteCurrentPhoto($user);

            $user->foto_perfil = $path;
        }

        $user->save();

        $responseUser = $user->fresh();
        if ($responseUser->foto_perfil) {
            if (!str_starts_with($responseUser->foto_perfil, '/storage/')) {
                $responseUser->foto_perfil = Storage::disk('public')->url($responseUser->foto_perfil);
            }
        }

        return response()->json([
            'message' => 'Perfil actualizado',
            'user'    => $responseUser,
        ]);
    }

    private function safeDeleteCurrentPhoto($user): void
    {
        if (!$user->foto_perfil) return;

        $disk = Storage::disk('public');

        $path = $user->foto_perfil;

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        }

        if (str_starts_with($path, 'uploads/') && $disk->exists($path)) {
            $disk->delete($path);
        }
    }
}