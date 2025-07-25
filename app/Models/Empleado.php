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

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pagos() {
        return $this->hasMany(PagosEmpleado::class);
    }
}