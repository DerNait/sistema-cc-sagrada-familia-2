<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Crud\CrudControllerBase;
use App\Models\Bolsa;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BolsasController extends CrudControllerBase
{
    protected function configure(Request $request): void
    {
        $this->model(Bolsa::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('nombre')
            ->label('Nombre de la Bolsa')
            ->rules(['required', 'string', 'max:255']);

        $this->column('precio_total')
            ->label('Precio total (Q)')
            ->type('numeric')
            ->readonly();

        // Visible solo en tabla (lista)
        $this->column('productos_lista')
            ->label('Productos incluidos')
            ->readonly();

        // Visible solo en formulario
        $productosOpts = Producto::orderBy('nombre')->pluck('nombre', 'id')->toArray();

        $this->column('productos_id')
            ->label('Productos')
            ->type('relation')
            ->filterable('select')
            ->filterOptions($productosOpts)
            ->options($productosOpts)
            ->rules(['array', 'exists:productos,id'])
            ->pivot('productos')
            ->multiRelation()
            ->hide(true, 'table'); 

        $this->column('created_at')
            ->label('Creado en')
            ->type('timestamp')
            ->readonly()
            ->hide();

        $this->column('updated_at')
            ->label('Actualizado en')
            ->type('timestamp')
            ->readonly()
            ->hide();

        $this->syncAbilities('admin.bolsas');
    }

    protected function query()
    {
        return Bolsa::with('productos');
    }

    public function store(Request $request)
    {
        $this->configure($request);
        abort_unless($this->abilities['create'] ?? false, 403);

        $data = $this->validatedData($request);
        $productosIds = $request->input('productos_id', []);

        $total = 0;
        if (!empty($productosIds)) {
            $total = Producto::whereIn('id', $productosIds)->sum('precio_unitario');
        }
        $data['precio_total'] = $total;

        $item = DB::transaction(function () use ($data, $request) {
            $bolsa = Bolsa::create($data);
            $this->syncPivotRelations($request, $bolsa);
            return $bolsa;
        });

        return $item->refresh();
    }

    public function update(Request $request, $id)
    {
        $this->configure($request);
        abort_unless($this->abilities['update'] ?? false, 403);

        $data = $this->validatedData($request);
        $bolsa = Bolsa::findOrFail($id);
        $productosIds = $request->input('productos_id', []);

        $total = 0;
        if (!empty($productosIds)) {
            $total = Producto::whereIn('id', $productosIds)->sum('precio_unitario');
        }
        $data['precio_total'] = $total;

        DB::transaction(function () use ($bolsa, $data, $request) {
            $bolsa->update($data);
            $this->syncPivotRelations($request, $bolsa);
        });

        return $bolsa->refresh();
    }
}
