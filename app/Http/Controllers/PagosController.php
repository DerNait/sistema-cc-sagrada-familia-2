<?php

namespace App\Http\Controllers;

use App\Models\EstudiantePago;
use App\Models\GradoPrecio;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $rolId = $user->rol_id;

        // Si es admin (1) o secretaria (2), mostrar vista de administración
        if ($rolId === 1 || $rolId === 2) {
            // Obtener todos los pagos con las relaciones necesarias
            $pagos = EstudiantePago::with(['estudiante.usuario'])
                ->whereNotNull('comprobante')
                ->whereNull('aprobado_en')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($pago) {
                    $usuario = $pago->estudiante->usuario ?? null;
                    
                    return [
                        'id' => $pago->id,
                        'nombre' => $usuario->name ?? 'N/A',
                        'apellido' => $usuario->apellido ?? 'N/A',
                        'correo' => $usuario->email ?? 'N/A',
                        'meses_pagados' => $pago->meses_pagados,
                        'monto_pagado' => 'Q' . $pago->monto_pagado,
                        'periodo_inicio' => $pago->periodo_inicio->format('Y-m-d'),
                        'periodo_fin' => $pago->periodo_fin->format('Y-m-d'),
                        'fecha_registro' => $usuario->fecha_registro ?? ($usuario ? $usuario->created_at->format('Y-m-d') : 'N/A'),
                        'comprobante' => $pago->comprobante ?? null
                    ];
                });

            $params = [
                'pagos' => $pagos
            ];

            return view('component', [
                'component' => 'admin-pago',
                'params'    => $params,
            ]);
        }
        
        // Si es estudiante (5), mostrar vista de estudiante
        elseif ($rolId === 5) {
            $tiposPago = TipoPago::select('id','nombre')->orderBy('nombre')->get();

            $estudiante = auth()->user()->estudiante;

            $estado = EstudiantePago::estadoActual(
                $estudiante->id,
                null,
                optional(auth()->user())->created_at ?? $estudiante->created_at
            );

            $params = [
                'tipos_pago'    => $tiposPago,
                'estado_pago' => $estado,
            ];

            return view('component', [
                'component' => 'estudiante-pago',
                'params'    => $params,
            ]);
        }
        
        // Si es cualquier otro rol (docente, inventario, etc.), denegar acceso
        else {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'path'           => ['required', 'string'],
            'meses_pagados'  => ['nullable', 'integer', 'min:1', 'max:12'],
            'tipo_pago_id'   => ['required', 'integer', Rule::exists('tipo_pagos','id')],
            'monto_pagado'   => ['required', 'numeric', 'min:0.01'],
        ]);

        // Sanitiza y mueve el archivo
        $tmpPath = ltrim($data['path'], '/');
        if (str_contains($tmpPath, '..') || !str_starts_with($tmpPath, 'uploads/')) {
            return response()->json(['message' => 'Ruta de archivo inválida.'], 422);
        }
        $disk = Storage::disk('public');
        if (!$disk->exists($tmpPath)) {
            return response()->json(['message' => 'El archivo no existe (quizá ya fue limpiado).'], 422);
        }

        $filename = basename($tmpPath);
        $destDir  = 'uploads/comprobantes';
        $destPath = $destDir . '/' . $filename;
        if ($disk->exists($destPath)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext  = pathinfo($filename, PATHINFO_EXTENSION);
            $destPath = $destDir . '/' . $name . '-' . uniqid() . ($ext ? ".{$ext}" : '');
        }
        $disk->move($tmpPath, $destPath);
        $publicUrl = $disk->url($destPath);

        $meses = (int) ($data['meses_pagados'] ?? 1);
        $today = Carbon::today();

        // Resuelve estudiante/grado
        $estudiante   = auth()->user()->estudiante()->firstOrFail();
        $estudianteId = $estudiante->id;

        $gradoId = $estudiante->grado_id ?? $estudiante->secciones()
            ->select('secciones.grado_id')
            ->orderByDesc('seccion_estudiantes.created_at')
            ->value('secciones.grado_id');

        if (!$gradoId) {
            return response()->json(['message' => 'El estudiante no tiene sección o grado asignado.'], 422);
        }

        $gradoPrecioId = GradoPrecio::where('grado_id', $gradoId)->value('id');
        if (!$gradoPrecioId) {
            return response()->json([
                'message' => "No hay precio configurado para el grado {$gradoId}. Crea un registro en grado_precio."
            ], 422);
        }

        $tipoPagoId  = $data['tipo_pago_id'];
        $montoPagado = $data['monto_pagado'];

        // --- AQUÍ VIENEN TUS REGLAS NUEVAS ---
        $nuevoInicio = $today->copy();           // por defecto hoy
        $nuevoFin    = $today->copy()->addMonths($meses)->subDay();

        DB::transaction(function () use (
            $estudianteId, $today, $meses, &$nuevoInicio, &$nuevoFin
        ) {
            $actuales = EstudiantePago::where('estudiante_id', $estudianteId)
                ->whereDate('periodo_fin',    '>=', $today->toDateString())
                ->orderByDesc('periodo_fin')
                ->get();

            $pendienteActual = $actuales->firstWhere('aprobado_en', null);
            $aprobadoActual  = $actuales->firstWhere('aprobado_en', '!=', null);

            // Regla 2: si hay pendiente activo → eliminarlo
            if ($pendienteActual) {
                if ($pendienteActual->comprobante) { Storage::disk('public')->delete(parse_url($pendienteActual->comprobante, PHP_URL_PATH)); }
                $pendienteActual->delete();
            }

            // Regla 1: si hay aprobado activo → encadenar desde su fin
            if ($aprobadoActual) {
                $base = $aprobadoActual->periodo_fin->copy()->addDay(); // día siguiente al fin actual
                $nuevoInicio = $base;
                $nuevoFin    = $base->copy()->addMonths($meses)->subDay();
            }
        });

        $periodoInicio = $nuevoInicio->toDateString();
        $periodoFin    = $nuevoFin->toDateString();

        // Crear el NUEVO pendiente
        $pago = new EstudiantePago();
        $pago->grado_precio_id = $gradoPrecioId;
        $pago->estudiante_id   = $estudianteId;
        $pago->tipo_pago_id    = $tipoPagoId;
        $pago->monto_pagado    = $montoPagado;
        $pago->meses_pagados   = $meses;
        $pago->periodo_inicio  = $periodoInicio;
        $pago->periodo_fin     = $periodoFin;

        $pago->tipo_estado_id  = 1;      // pendiente
        $pago->aprobado_id     = null;
        $pago->aprobado_en     = null;
        $pago->comprobante     = $publicUrl;

        $pago->save();

        $estado = EstudiantePago::estadoActual(
            $estudiante->id,
            null,
            optional(auth()->user())->created_at ?? $estudiante->created_at
        );

        return response()->json([
            'message'         => 'Pago enviado correctamente. Quedó en revisión.',
            'pago_id'         => $pago->id,
            'comprobante_url' => $publicUrl,
            'periodo_inicio'  => $periodoInicio,
            'periodo_fin'     => $periodoFin,
            'estado_pago'     => $estado,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $pago = EstudiantePago::findOrFail($id);
            
            // Validar datos de entrada
            $data = $request->validate([
                'tipo_estado_id' => ['required', 'integer', 'in:2,3'], // Solo aceptar aprobado (2) o rechazado (3)
            ]);

            // Actualizar el estado del pago
            $pago->tipo_estado_id = $data['tipo_estado_id'];
            
            // Si se está aprobando (estado "Completado" = 2), agregar datos del aprobador
            if ($data['tipo_estado_id'] == 2) {
                $pago->aprobado_id = Auth::id();
                $pago->aprobado_en = now();
            } 
            // Si se está rechazando (estado "Cancelado" = 3), también registrar quién lo hizo
            elseif ($data['tipo_estado_id'] == 3) {
                $pago->aprobado_id = Auth::id();
                $pago->aprobado_en = now();
            }
            
            $pago->save();

            $mensaje = match($data['tipo_estado_id']) {
                2 => 'Pago aprobado correctamente',
                3 => 'Pago rechazado correctamente', 
                default => 'Estado del pago actualizado'
            };

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'pago' => $pago
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pago = EstudiantePago::findOrFail($id);
            
            // Opcional: Eliminar también el archivo del comprobante
            if ($pago->comprobante) {
                $path = str_replace('/storage/', '', parse_url($pago->comprobante, PHP_URL_PATH));
                Storage::disk('public')->delete($path);
            }
            
            $pago->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pago eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}