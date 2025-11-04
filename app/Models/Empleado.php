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

    protected $appends = ['nombre_completo', 'rol_nombre'];

    
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    
    public function pagos() {
        return $this->hasMany(PagosEmpleado::class);
    }

    
    public function scopeDocentes($q, $rolDocenteId = 4)
    {
        return $q->whereHas('user', fn($u) => $u->where('rol_id', $rolDocenteId));
    }

    
    public function getNombreCompletoAttribute()
    {
        return trim(($this->user->name ?? '') . ' ' . ($this->user->apellido ?? ''));
    }


    public function getRolNombreAttribute()
    {
        return $this->user?->role?->nombre ?? '';
    }
}
