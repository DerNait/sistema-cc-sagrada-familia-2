<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bolsa extends Model
{
    protected $guarded = ['id'];

    // 👇 Para que el CRUD lea estos atributos calculados
    protected $appends = ['productos_lista', 'productos_id'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'bolsas_detalles');
    }

    public function getProductosListaAttribute(): string
    {
        return $this->productos->pluck('nombre')->implode(', ');
    }

    public function getProductosIdAttribute(): array
    {
        return $this->productos->pluck('id')->toArray();
    }
}
