<?php
namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Empleado;
use App\Models\User;
use App\Models\Role;
use App\Models\PagosEmpleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class EmpleadosController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Empleado::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('usuario_id')
            ->label('Usuario')
            ->type('relation')
            ->rules(['required'])
            ->options(
                User::orderBy('name')
                    ->get()
                    ->mapWithKeys(fn($u)=>[$u->id => "{$u->name} {$u->apellido}"])
                    ->toArray()
            );

        $this->column('rol_nombre')
            ->label('Rol')
            ->readonly();

        $this->column('salario_base')
            ->label('Salario')
            ->type('numeric')
            ->rules(['required','numeric','min:0']);

        $this->column('creado_en')
            ->label('Creado en')
            ->readonly();

        $this->syncAbilities('admin.empleados');
    }

    public function edit(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        // Cargar el item con las relaciones necesarias
        $item = $this->newModelQuery()
            ->with(['user.role']) // Cargar user y su relación role
            ->findOrFail($id);

        $params = [
            'item'    => $item,
            'columns' => $this->columns,
            'action'  => route(Str::beforeLast($request->route()->getName(), '.') . '.update', $item->getKey()),
        ];

        return view('component', [
            'component' => 'crud-form',
            'params'    => $params
        ]);
    }

    public function planilla()
    {
        $periodo = PagosEmpleado::select(DB::raw('MAX(periodo_anio) as anio'), DB::raw('MAX(periodo_mes) as mes'))->first();

        if (!$periodo || !$periodo->anio || !$periodo->mes) {
            abort(404, 'No hay registros de pagos para generar la planilla.');
        }

        $empleados = Empleado::with(['user', 'role', 'pagos' => function ($q) use ($periodo) {
            $q->where('periodo_anio', $periodo->anio)
            ->where('periodo_mes', $periodo->mes)
            ->with('ajustes.tipo');
        }])->get();

        $datosPlanilla = $empleados->map(function ($empleado) {
            $pago = $empleado->pagos->first();

            if (!$pago) return null;

            $ajustesPositivos = $pago->ajustes->filter(function ($ajuste) {
                $nombre = strtolower($ajuste->tipo->ajuste);
                return str_contains($nombre, 'bono') ||
                    str_contains($nombre, 'incentivo') ||
                    str_contains($nombre, 'extra') ||
                    str_contains($nombre, 'premio') ||
                    str_contains($nombre, 'productividad') ||
                    str_contains($nombre, 'asistencia');
            })->sum('monto');

            $ajustesNegativos = $pago->ajustes->filter(function ($ajuste) {
                $nombre = strtolower($ajuste->tipo->ajuste);
                return str_contains($nombre, 'descuento') ||
                    str_contains($nombre, 'sanción') ||
                    str_contains($nombre, 'deducción') ||
                    str_contains($nombre, 'prestamo') ||
                    str_contains($nombre, 'anticipo');
            })->sum('monto');

            return [
                'name'           => $empleado->user->name,
                'dpi'            => $empleado->dpi,
                'cargo'          => $empleado->user->role->nombre,
                'fecha_ingreso'  => $empleado->fecha_ingreso,
                'estado'         => $empleado->estado_pago_mes_actual,
                'salario_base'   => $pago->monto_base,
                'bonificaciones' => $ajustesPositivos,
                'descuentos'     => $ajustesNegativos,
                'total'          => $pago->monto_total,
            ];
        })->filter();

        $pdf = Pdf::loadView('empleados.planilla', [
            'datosPlanilla' => $datosPlanilla,
            'periodo' => $periodo,
        ]);

        return $pdf->download("planilla_salarios_{$periodo->anio}_{$periodo->mes}.pdf");
    }
}
