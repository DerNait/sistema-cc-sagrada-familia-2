<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstudianteNota;

class NotasController extends Controller
{
    /**
     * Almacena una nueva nota
     */
    public function store(Request $request)
    {
        $rules = [
            'seccion_estudiante_id' => [
                'required',
                'exists:seccion_estudiantes,id',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = EstudianteNota::where('seccion_estudiante_id', $value)
                        ->where('actividad_id', $request->actividad_id)
                        ->exists();

                    if ($exists) {
                        $fail('Este estudiante ya tiene una nota registrada para esta actividad.');
                    }
                }
            ],
            'actividad_id' => 'required|exists:actividades,id',
            'nota' => 'required|numeric|min:0|max:100|decimal:0,2',
        ];

        $messages = [
            'nota.max' => 'La nota no puede ser mayor a 100',
            'nota.decimal' => 'La nota debe tener máximo 2 decimales'
        ];

        $request->validate($rules, $messages);

        $nota = new EstudianteNota();
        $nota->seccion_estudiante_id = $request->seccion_estudiante_id;
        $nota->actividad_id = $request->actividad_id;
        $nota->nota = $request->nota;
        $nota->save();

        return response()->json('ok');
    }

    /**
     * Actualiza una nota existente
     */
    public function update(Request $request, $id)
    {        
        $nota = EstudianteNota::findOrFail($id);

        $rules = [
            'nota' => 'required|numeric|min:0|max:100|decimal:0,2',
            'actividad_id' => 'sometimes|required|exists:actividades,id',
        ];

        $messages = [
            'nota.max' => 'La nota no puede ser mayor a 100',
            'nota.decimal' => 'La nota debe tener máximo 2 decimales'
        ];

        $request->validate($rules, $messages);

        // Verificar unicidad si se cambia la actividad
        if ($request->has('actividad_id') && $request->actividad_id != $nota->actividad_id) {
            $exists = EstudianteNota::where('seccion_estudiante_id', $nota->seccion_estudiante_id)
                ->where('actividad_id', $request->actividad_id)
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Este estudiante ya tiene una nota para la nueva actividad seleccionada'], 422);
            }

            $nota->actividad_id = $request->actividad_id;
        }

        $nota->nota = $request->nota;
        $nota->save();

        return response()->json('ok');
    }
}
