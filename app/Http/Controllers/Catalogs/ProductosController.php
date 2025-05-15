<?php

namespace App\Http\Controllers\Catalogs;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Http\Controllers\Crud\CrudControllerBase;

class ProductosController extends CrudControllerBase
{
    protected function configure(Request $request): void {
        $this->model(Producto::class);

        $this->column('id')
            ->label('ID')
            ->readonly();

        $this->column('tipo_producto_id')
            ->label('Tipo de Producto')
            ->type('relation')
            ->rules(['required'])
            ->options(
                TipoProducto::orderBy('nombre')
                    ->get()
                    ->mapWithKeys(fn($t)=>[$t->id => "{$t->nombre}"])
                    ->toArray()
            );

        $this->column('nombre')
            ->label('Nombre')
            ->rules(['required','string','max:255']);

        $this->column('created_at')
            ->label('Creado en')
            ->type('datetime')
            ->readonly();

        $this->syncAbilities('productos');
    }
}

