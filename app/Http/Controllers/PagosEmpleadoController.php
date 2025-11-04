<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\PagosEmpleado;
use App\Models\TipoEstado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class PagosEmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $rolId = $user->rol_id;
        if ($rolId !== 1 && $rolId !== 2) abort(403);

        $mes  = (int) ($request->query('mes')  ?? now()->month);
        $anio = (int) ($request->query('anio') ?? now()->year);

        $tiposEstado  = TipoEstado::select('id','tipo')->orderBy('tipo')->get();
        $idPendiente  = (int) TipoEstado::where('tipo','Pendiente')->value('id');
        $idCompletado = (int) TipoEstado::where('tipo','Completado')->value('id');

        $pagosPeriodo = PagosEmpleado::with(['empleado.user','tipoEstado'])
            ->where('periodo_mes', $mes)
            ->where('periodo_anio', $anio)
            ->get()
            ->keyBy('empleado_id');

        $empleados = Empleado::with('user')->get();

        $rows = $empleados->map(function ($emp) use ($pagosPeriodo, $mes, $anio, $idPendiente) {
            $pago = $pagosPeriodo->get($emp->id);

            $nombre   = $emp->user->name     ?? 'N/A';
            $apellido = $emp->user->apellido ?? 'N/A';
            $correo   = $emp->user->email    ?? 'N/A';
            $salarioBaseEmpleado = (float) ($emp->salario_base ?? 0);

            if ($pago) {
                return [
                    'id'                  => $pago->id,
                    'empleado_id'         => $emp->id,
                    'nombre'              => $nombre,
                    'apellido'            => $apellido,
                    'correo'              => $correo,
                    'fecha_ingreso'       => optional($pago->fecha_ingreso)->format('Y-m-d'),
                    'salario_base'        => $pago->salario_base ?? $salarioBaseEmpleado,
                    'bonificacion_ley'    => $pago->bonificacion_ley,
                    'bonificacion_extra'  => $pago->bonificacion_extra,
                    'descuento_igss'      => $pago->descuento_igss,
                    'descuentos_varios'   => $pago->descuentos_varios,
                    'total'               => $pago->total,
                    'tipo_estado_id'      => $pago->tipo_estado_id,
                    'tipo_estado_nombre'  => optional($pago->tipoEstado)->tipo ?? 'Pendiente',
                    'periodo_mes'         => $pago->periodo_mes,
                    'periodo_anio'        => $pago->periodo_anio,
                    'periodo_label'       => sprintf('%02d/%d', $pago->periodo_mes, $pago->periodo_anio),
                ];
            }

            return [
                'id'                  => null,
                'empleado_id'         => $emp->id,
                'nombre'              => $nombre,
                'apellido'            => $apellido,
                'correo'              => $correo,
                'fecha_ingreso'       => null,
                'salario_base'        => $salarioBaseEmpleado,
                'bonificacion_ley'    => 0,
                'bonificacion_extra'  => 0,
                'descuento_igss'      => 0,
                'descuentos_varios'   => 0,
                'total'               => null,
                'tipo_estado_id'      => $idPendiente,
                'tipo_estado_nombre'  => 'Pendiente',
                'periodo_mes'         => $mes,
                'periodo_anio'        => $anio,
                'periodo_label'       => sprintf('%02d/%d', $mes, $anio),
            ];
        })->values();

        return view('component', [
            'component' => 'empleado-pago',
            'params'    => [
                'rows'         => $rows,
                'tipos_estado' => $tiposEstado,
                'periodo'      => ['mes' => $mes, 'anio' => $anio],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $estados = TipoEstado::pluck('id','tipo');
        $idCompletado = (int) ($estados['Completado'] ?? 0);

        $hasAprobadoId = Schema::hasColumn('pagos_empleados', 'aprobado_id');
        $hasAprobadoEn = Schema::hasColumn('pagos_empleados', 'aprobado_en');

        // 1) Si viene un ID → marcar ese registro como Completado
        $pagoId = $request->input('id') ?? $request->input('pago_id');
        if ($pagoId) {
            $pago = PagosEmpleado::findOrFail($pagoId);
            $pago->tipo_estado_id = $idCompletado;

            if ($hasAprobadoId) $pago->aprobado_id = Auth::id();
            if ($hasAprobadoEn) $pago->aprobado_en = now();

            $pago->updated_at = now();
            $pago->save();

            return response()->json([
                'id'      => $pago->id,
                'total'   => $pago->total,
                'success' => true,
            ]);
        }

        // 2) Sin ID → validación completa para crear/actualizar por periodo
        $data = $request->validate([
            'empleado_id'        => ['required','integer','exists:empleados,id'],
            'periodo_mes'        => ['required','integer','min:1','max:12'],
            'periodo_anio'       => ['required','integer','min:2000'],
            'salario_base'       => ['required','numeric','min:0'],
            'bonificacion_ley'   => ['nullable','numeric','min:0'],
            'bonificacion_extra' => ['nullable','numeric','min:0'],
            'descuento_igss'     => ['nullable','numeric','min:0'],
            'descuentos_varios'  => ['nullable','numeric','min:0'],
        ]);

        $data['total']          = PagosEmpleado::calcularTotal($data);
        $data['tipo_estado_id'] = $idCompletado; // crear/actualizar como Completado
        $data['updated_at']     = now();
        $data['created_at']     = now();

        $existing = PagosEmpleado::where('empleado_id', $data['empleado_id'])
            ->where('periodo_mes',  $data['periodo_mes'])
            ->where('periodo_anio', $data['periodo_anio'])
            ->first();

        if ($existing) {
            // Actualiza montos + estado
            $existing->fill($data);
            if ($hasAprobadoId) $existing->aprobado_id = Auth::id();
            if ($hasAprobadoEn) $existing->aprobado_en = now();
            $existing->save();

            return response()->json([
                'id'      => $existing->id,
                'total'   => $existing->total,
                'success' => true,
            ]);
        }

        $pago = PagosEmpleado::create($data);

        // Setea aprobador/fecha si existen columnas
        $touched = false;
        if ($hasAprobadoId) { $pago->aprobado_id = Auth::id(); $touched = true; }
        if ($hasAprobadoEn) { $pago->aprobado_en = now();      $touched = true; }
        if ($touched) $pago->save();

        return response()->json([
            'id'      => $pago->id,
            'total'   => $pago->total,
            'success' => true,
        ]);
    }

    public function show($id)
    {
        $pago = PagosEmpleado::with(['empleado.user','tipoEstado','ajustes.tipoAjuste'])->findOrFail($id);
        $usuario = $pago->empleado->user ?? null;

        return response()->json([
            'id'                 => $pago->id,
            'empleado_id'        => $pago->empleado_id,
            'nombre'             => $usuario->name ?? 'N/A',
            'apellido'           => $usuario->apellido ?? 'N/A',
            'correo'             => $usuario->email ?? 'N/A',
            'fecha_ingreso'      => optional($pago->fecha_ingreso)->format('Y-m-d'),
            'salario_base'       => $pago->salario_base,
            'bonificacion_ley'   => $pago->bonificacion_ley,
            'bonificacion_extra' => $pago->bonificacion_extra,
            'descuento_igss'     => $pago->descuento_igss,
            'descuentos_varios'  => $pago->descuentos_varios,
            'total'              => $pago->total,
            'tipo_estado_id'     => $pago->tipo_estado_id,
            'tipo_estado_nombre' => optional($pago->tipoEstado)->tipo ?? 'N/A',
            'ajustes'            => $pago->ajustes->map(fn($a) => [
                'tipo'        => $a->tipoAjuste->ajuste,
                'descripcion' => $a->descripcion,
                'monto'       => $a->monto,
            ]),
            'periodo_mes'        => $pago->periodo_mes,
            'periodo_anio'       => $pago->periodo_anio,
        ]);
    }

    public function update(Request $request, $id)
    {
        $pago = PagosEmpleado::findOrFail($id);
        $estados = TipoEstado::pluck('id','tipo');

        $data = $request->validate([
            'tipo_estado_id' => ['required','integer','in:'.implode(',', $estados->values()->all())],
        ]);

        $nuevo = (int) $data['tipo_estado_id'];
        $pago->tipo_estado_id = $nuevo;

        $hasAprobadoId = Schema::hasColumn('pagos_empleados', 'aprobado_id');
        $hasAprobadoEn = Schema::hasColumn('pagos_empleados', 'aprobado_en');

        if (($estados['Pendiente'] ?? null) !== null && $nuevo === (int) $estados['Pendiente']) {
            if ($hasAprobadoId) $pago->aprobado_id = null;
            if ($hasAprobadoEn) $pago->aprobado_en = null;
            $pago->save();

            return response()->json(['success' => true, 'pago' => $pago]);
        }

        if (($estados['Completado'] ?? null) !== null && $nuevo === (int) $estados['Completado']) {
            if ($hasAprobadoId) $pago->aprobado_id = Auth::id();
            if ($hasAprobadoEn) $pago->aprobado_en = now();
        }

        $pago->save();

        return response()->json(['success' => true, 'pago' => $pago]);
    }
}
