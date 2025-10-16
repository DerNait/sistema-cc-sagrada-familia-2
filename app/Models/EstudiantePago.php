<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EstudiantePago extends Model
{
    protected $table = 'estudiante_pagos';

    protected $guarded = ['id'];

    protected $casts = [
        'periodo_inicio' => 'date',
        'periodo_fin'    => 'date',
        'aprobado_en'    => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function gradoPrecio()
    {
        return $this->belongsTo(GradoPrecio::class);
    }

    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class);
    }

    public function tipoEstado()
    {
        return $this->belongsTo(TipoEstado::class);
    }

    public function scopeDelEstudiante($q, int $estudianteId) 
    { 
        return $q->where('estudiante_id', $estudianteId); 
    }

    public function getEstaAprobadoAttribute(): bool
    {
        return !is_null($this->aprobado_en);
    }

    public static function estadoActual(int $estudianteId, ?Carbon $hoy = null, ?Carbon $fechaAlta = null): array
    {
        $hoy = $hoy ?? Carbon::today();

        $actuales = self::delEstudiante($estudianteId)
            ->whereDate('periodo_fin', '>=', $hoy->toDateString())
            ->get();

        $tienePendienteExtra = self::delEstudiante($estudianteId)
            ->whereDate('periodo_fin', '>=', $hoy->toDateString())
            ->whereNull('aprobado_en')
            ->exists();

        if ($actuales->isNotEmpty()) {
            $aprobado = $actuales->filter->estaAprobado->sortByDesc('periodo_fin')->first();
            if ($aprobado) {
                return [
                    'estado'  => 'vigente',
                    'mensaje' => 'Vigente hasta el '.$aprobado->periodo_fin->isoFormat('D [de] MMMM'),
                    'pendiente_extra' => $tienePendienteExtra,
                    'pendiente_extra_mensaje' => $tienePendienteExtra ? 'Tienes un pago pendiente de revisión' : null,
                ];
            }

            return [
                'estado'  => 'pendiente',
                'mensaje' => 'Pendiente de revisión',
                'pendiente_extra' => false,
                'pendiente_extra_mensaje' => null,
            ];
        }

        $ultimoPago = self::delEstudiante($estudianteId)
            ->whereNotNull('periodo_fin')
            ->orderByDesc('periodo_fin')
            ->first();

        if ($ultimoPago) {
            return [
                'estado'  => 'vencido',
                'mensaje' => 'Vencido desde el '.$ultimoPago->periodo_fin->isoFormat('D [de] MMMM'),
                'pendiente_extra' => $tienePendienteExtra,
                'pendiente_extra_mensaje' => $tienePendienteExtra ? 'Tienes un pago pendiente de revisión' : null,
            ];
        }

        $desde = ($fechaAlta ?? Carbon::today())->isoFormat('D [de] MMMM');
        return [
            'estado'  => 'vencido',
            'mensaje' => 'Vencido desde el '.$desde,
            'pendiente_extra' => $tienePendienteExtra,
            'pendiente_extra_mensaje' => $tienePendienteExtra ? 'Tienes un pago pendiente de revisión' : null,
        ];
    }
}
