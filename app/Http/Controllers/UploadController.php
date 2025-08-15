<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
            'path' => 'nullable|string',
        ]);

        $folder = trim($request->input('path', 'uploads'), '/');
        if (str_contains($folder, '..')) {
            return response()->json(['message' => 'Ruta inválida'], 422);
        }

        $storedPath = $request->file('file')->store($folder, 'public');

        $publicUrl    = Storage::disk('public')->url($storedPath);
        $decoratedUrl = '/' . ltrim($storedPath, '/');

        return response()->json([
            'message'       => 'Archivo subido correctamente',
            'url'           => $publicUrl,
            'url_decorated' => $decoratedUrl,
            'path'          => $storedPath,
        ]);
    }
}
