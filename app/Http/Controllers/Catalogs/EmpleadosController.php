<?php
namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Empleado;
use App\Models\User;
use App\Models\Role;
use App\Models\PagosEmpleado;
use Illuminate\Support\Facades\DB;

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

        $this->column('user.role.nombre')
            ->label('Rol')
            ->readonly();

        $this->column('user.role.id')
            ->label('Filtrar por rol')
            ->type('relation')
            ->filterable('select')
            ->filterOptions(
                Role::orderBy('nombre')
                    ->pluck('nombre','id')
                    ->toArray()
            )
            ->hide()
            ->readonly();


        $this->column('salario_base')
            ->label('Salario')
            ->type('numeric')
            ->rules(['required','numeric','min:0']);

        $this->column('created_at')
            ->label('Creado en')
            ->type('datetime')
            ->readonly();

        $this->syncAbilities('admin.empleados');
    }

    public function planilla()
    {
        $periodo = PagosEmpleado::select(DB::raw('MAX(periodo_anio) as anio'), DB::raw('MAX(periodo_mes) as mes'))->first();

        if (!$periodo || !$periodo->anio || !$periodo->mes) {
            abort(404, 'No hay registros de pagos para generar la planilla.');
        }

        $empleados = Empleado::with(['usuario', 'role', 'pagos' => function ($q) use ($periodo) {
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
                'name'           => $empleado->usuario->name,
                'dpi'            => $empleado->dpi,
                'cargo'          => $empleado->role?->nombre,
                'cuenta'         => $empleado->numero_cuenta,
                'banco'          => $empleado->banco,
                'fecha_ingreso'  => $empleado->fecha_ingreso,
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