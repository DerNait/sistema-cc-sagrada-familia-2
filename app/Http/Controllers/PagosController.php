<?php

namespace App\Http\Controllers;

use App\Models\EstudiantePago;
use App\Models\GradoPrecio;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PagosController extends Controller
{
    public function index()
    {
        $tiposPago = TipoPago::select('id','nombre')->orderBy('nombre')->get();

        $params = [
            'tipos_pago' => $tiposPago,
        ];

        return view('component', [
            'component' => 'estudiante-pago',
            'params'    => $params,
        ]);
    }

    public function store(Request $request)
    {
        // 1) Validar entrada mínima
        $data = $request->validate([
            'path'           => ['required', 'string'],
            'meses_pagados'  => ['nullable', 'integer', 'min:1', 'max:12'],
            'tipo_pago_id'   => ['required', 'integer', Rule::exists('tipo_pagos','id')],
            'monto_pagado'   => ['required', 'numeric', 'min:0.01'],
        ]);

        // 2) Sanitizar y mover el archivo a carpeta final
        $tmpPath = ltrim($data['path'], '/');

        if (str_contains($tmpPath, '..') || !str_starts_with($tmpPath, 'uploads/')) {
            return response()->json(['message' => 'Ruta de archivo inválida.'], 422);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($tmpPath)) {
            return response()->json(['message' => 'El archivo no existe (quizá ya fue limpiado).'], 422);
        }

        // Mover a carpeta "definitiva"
        $filename = basename($tmpPath);
        $destDir  = 'uploads/comprobantes';
        $destPath = $destDir . '/' . $filename;

        // Evitar colisiones simples
        if ($disk->exists($destPath)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext  = pathinfo($filename, PATHINFO_EXTENSION);
            $destPath = $destDir . '/' . $name . '-' . uniqid() . ($ext ? ".{$ext}" : '');
        }

        $disk->move($tmpPath, $destPath);
        $publicUrl = $disk->url($destPath);

        // 3) Defaults razonables si aún no mandas todo desde la UI
        $meses = (int) ($data['meses_pagados'] ?? 1);
        $today = now()->startOfDay();

        $periodoInicio = $data['periodo_inicio'] ?? $today->toDateString();
        $periodoFin    = $data['periodo_fin']    ?? $today->copy()->addMonths($meses)->subDay()->toDateString();

        // Resolver estudiante + grado desde la sección (vía pivote)
        $estudiante = auth()->user()->estudiante()->firstOrFail();
        $estudianteId = $estudiante->id;

        // Usa el accessor nuevo; si por algo es null, cae al query directo
        $gradoId = $estudiante->grado_id ?? $estudiante->secciones()
            ->select('secciones.grado_id')
            ->orderByDesc('seccion_estudiantes.created_at')
            ->value('secciones.grado_id');

        if (!$gradoId) {
            return response()->json(['message' => 'El estudiante no tiene sección o grado asignado.'], 422);
        }

        // OJO: tu tabla es singular 'grado_precio'
        $gradoPrecioId = GradoPrecio::where('grado_id', $gradoId)->value('id');

        if (!$gradoPrecioId) {
            return response()->json([
                'message' => "No hay precio configurado para el grado {$gradoId}. Crea un registro en grado_precio."
            ], 422);
        }

        $tipoPagoId     = $data['tipo_pago_id'] ?? null;
        $montoPagado    = $data['monto_pagado'] ?? null;

        // 4) Crear el registro
        $pago = new EstudiantePago();
        $pago->grado_precio_id = $gradoPrecioId;
        $pago->estudiante_id   = $estudianteId;
        $pago->tipo_pago_id    = $tipoPagoId;
        $pago->monto_pagado    = $montoPagado;
        $pago->meses_pagados   = $meses;
        $pago->periodo_inicio  = $periodoInicio;
        $pago->periodo_fin     = $periodoFin;

        $pago->tipo_estado_id  = 1;
        $pago->aprobado_id     = null;
        $pago->aprobado_en     = null;
        $pago->comprobante     = $publicUrl;

        $pago->save();

        return response()->json([
            'message' => 'Pago enviado correctamente. Quedó en revisión.',
            'pago_id' => $pago->id,
            'comprobante_url' => $publicUrl,
        ]);
    }
}
