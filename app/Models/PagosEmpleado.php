<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PagosEmpleado extends Model
{
    protected $table = 'pagos_empleados';

    // Si no usas fillable, al menos usa guarded vacío para asignación masiva controlada en controladores/requests
    protected $guarded = [];

    // Si prefieres explícito:
    // protected $fillable = [
    //     'empleado_id','nombre','fecha_ingreso',
    //     'periodo_mes','periodo_anio','tipo_estado_id',
    //     'salario_base','bonificacion_ley','bonificacion_extra',
    //     'descuento_igss','descuentos_varios','total',
    // ];

    protected $casts = [
        'fecha_ingreso'    => 'date',
        'salario_base'     => 'decimal:2',
        'bonificacion_ley' => 'decimal:2',
        'bonificacion_extra'=> 'decimal:2',
        'descuento_igss'   => 'decimal:2',
        'descuentos_varios'=> 'decimal:2',
        'total'            => 'decimal:2',
        'periodo_mes'      => 'integer',
        'periodo_anio'     => 'integer',
    ];

    public $timestamps = true;

    // ─── Relaciones ─────────────────────────────────────────────────────────────
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function tipoEstado(): BelongsTo
    {
        return $this->belongsTo(TipoEstado::class, 'tipo_estado_id');
    }

    public function ajustes(): HasMany
    {
        return $this->hasMany(AjusteSalarial::class, 'pago_empleado_id');
    }

    // ─── Scopes útiles (opcional) ──────────────────────────────────────────────
    public function scopeForPeriod($q, int $mes, int $anio)
    {
        return $q->where('periodo_mes', $mes)->where('periodo_anio', $anio);
    }
}
