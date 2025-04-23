<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';

    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'salario_base',
        'created_at',
        'updated_at',
    ];

    public function usuario() {
        return $this->belongsTo(Usuario::class);
    }
    
    public function pagos() {
        return $this->hasMany(PagoEmpleado::class);
    }    
}
