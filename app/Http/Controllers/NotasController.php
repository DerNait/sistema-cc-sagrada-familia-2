<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstudianteNota;

class NotasController extends Controller
{
    /**
     * Crear o actualizar una nota y/o comentario
     * según seccion_estudiante_id + actividad_id
     */
    public function store(Request $request)
    {
        $rules = [
            'seccion_estudiante_id' => ['required', 'exists:seccion_estudiantes,id'],
            'actividad_id'          => ['required', 'exists:actividades,id'],
            'nota'                  => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'comentario'            => ['nullable', 'string', 'max:2000'],
        ];

        $data = $request->validate($rules, [
            'nota.max'     => 'La nota no puede ser mayor a 100.',
            'nota.decimal' => 'La nota debe tener máximo 2 decimales.',
        ]);

        if (
            (!array_key_exists('nota', $data) || $data['nota'] === null) &&
            (!array_key_exists('comentario', $data) || $data['comentario'] === null)
        ) {
            return response()->json(['error' => 'Debes enviar al menos una nota o un comentario.'], 422);
        }

        $nota = EstudianteNota::where('seccion_estudiante_id', $data['seccion_estudiante_id'])
            ->where('actividad_id', $data['actividad_id'])
            ->first();

        if (!$nota) {
            // crear
            $nota = new EstudianteNota();
            $nota->seccion_estudiante_id = $data['seccion_estudiante_id'];
            $nota->actividad_id          = $data['actividad_id'];
            // ✅ Si no enviaron nota al crear, default = 0
            $nota->nota = array_key_exists('nota', $data) && $data['nota'] !== null ? $data['nota'] : 0;
        } else {
            // actualizar existente
            // ✅ Solo tocar nota si viene en el request
            if (array_key_exists('nota', $data)) {
                $nota->nota = ($data['nota'] === null) ? 0 : $data['nota'];
            }
        }

        if (array_key_exists('comentario', $data)) {
            $nota->comentario = $data['comentario'];
        }

        $nota->save();

        return response()->json([
            'id'                    => $nota->id,
            'actividad_id'          => $nota->actividad_id,
            'seccion_estudiante_id' => $nota->seccion_estudiante_id,
            'nota'                  => $nota->nota,
            'comentario'            => $nota->comentario,
        ]);
    }

    /**
     * Actualizar nota y/o comentario por ID
     */
    public function update(Request $request, $cursoId, $notaId)
    {
        $nota = EstudianteNota::findOrFail($notaId);

        $rules = [
            'nota'         => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'comentario'   => ['nullable', 'string', 'max:2000'],
            'actividad_id' => ['sometimes', 'required', 'exists:actividades,id'],
        ];

        $data = $request->validate($rules, [
            'nota.max'     => 'La nota no puede ser mayor a 100.',
            'nota.decimal' => 'La nota debe tener máximo 2 decimales.',
        ]);

        if (!array_key_exists('nota', $data) && !array_key_exists('comentario', $data) && !array_key_exists('actividad_id', $data)) {
            return response()->json(['error' => 'No hay cambios que aplicar.'], 422);
        }

        if (array_key_exists('actividad_id', $data) && $data['actividad_id'] != $nota->actividad_id) {
            $exists = EstudianteNota::where('seccion_estudiante_id', $nota->seccion_estudiante_id)
                ->where('actividad_id', $data['actividad_id'])
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Ya existe una nota para esta actividad y este estudiante.'], 422);
            }

            $nota->actividad_id = $data['actividad_id'];
        }

        // ✅ Solo actualizar nota si viene en el request
        if (array_key_exists('nota', $data)) {
            // si quisieras permitir null => 0, hazlo aquí; si no, asigna tal cual
            $nota->nota = ($data['nota'] === null) ? 0 : $data['nota'];
        }

        if (array_key_exists('comentario', $data)) {
            $nota->comentario = $data['comentario'];
        }

        $nota->save();

        return response()->json([
            'id'                    => $nota->id,
            'actividad_id'          => $nota->actividad_id,
            'seccion_estudiante_id' => $nota->seccion_estudiante_id,
            'nota'                  => $nota->nota,
            'comentario'            => $nota->comentario,
        ]);
    }
}
