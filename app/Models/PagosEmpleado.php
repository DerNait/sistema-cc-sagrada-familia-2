<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    // Para exponer ya formateado en JSON / arrays
    protected $appends = ['periodo_label', 'periodo_rango'];

    // Si quieres ocultar las fechas crudas en las respuestas JSON, descomenta:
    // protected $hidden  = ['periodo_inicio', 'periodo_fin'];

    /* ==========================
     |  Relaciones
     ========================== */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function gradoPrecio(): BelongsTo
    {
        return $this->belongsTo(GradoPrecio::class);
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class);
    }

    public function tipoEstado(): BelongsTo
    {
        return $this->belongsTo(TipoEstado::class);
    }

    /* ==========================
     |  Scopes
     ========================== */
    public function scopeDelEstudiante($q, int $estudianteId)
    {
        return $q->where('estudiante_id', $estudianteId);
    }

    /* ==========================
     |  Accessors / Helpers
     ========================== */

    // ¿Está aprobado?
    public function getEstaAprobadoAttribute(): bool
    {
        return !is_null($this->aprobado_en);
    }

    // “noviembre 2025” tomando periodo_inicio
    public function getPeriodoLabelAttribute(): string
    {
        if (!$this->periodo_inicio) return '';
        return Carbon::parse($this->periodo_inicio)
            ->locale('es')
            ->translatedFormat('F Y');
    }

    // “noviembre 2025 – enero 2026” si abarca varios meses; si es 1 mes, muestra solo uno
    public function getPeriodoRangoAttribute(): string
    {
        if (!$this->periodo_inicio) return '';
        if (!$this->periodo_fin) return $this->periodo_label;

        $ini = Carbon::parse($this->periodo_inicio)->locale('es')->translatedFormat('F Y');
        $fin = Carbon::parse($this->periodo_fin)->locale('es')->translatedFormat('F Y');

        return $ini === $fin ? $ini : ($ini.' – '.$fin);
    }

    /* ==========================
     |  Lógica de estado actual
     ========================== */
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
                    'estado'                   => 'vigente',
                    'mensaje'                  => 'Vigente hasta el '.$aprobado->periodo_fin->isoFormat('D [de] MMMM'),
                    'pendiente_extra'          => $tienePendienteExtra,
                    'pendiente_extra_mensaje'  => $tienePendienteExtra ? 'Tienes un pago pendiente de revisión' : null,
                ];
            }

            return [
                'estado'                   => 'pendiente',
                'mensaje'                  => 'Pendiente de revisión',
                'pendiente_extra'          => false,
                'pendiente_extra_mensaje'  => null,
            ];
        }

        $ultimoPago = self::delEstudiante($estudianteId)
            ->whereNotNull('periodo_fin')
            ->orderByDesc('periodo_fin')
            ->first();

        if ($ultimoPago) {
            return [
                'estado'                   => 'vencido',
                'mensaje'                  => 'Vencido desde el '.$ultimoPago->periodo_fin->isoFormat('D [de] MMMM'),
                'pendiente_extra'          => $tienePendienteExtra,
                'pendiente_extra_mensaje'  => $tienePendienteExtra ? 'Tienes un pago pendiente de revisión' : null,
            ];
        }

        $desde = ($fechaAlta ?? Carbon::today())->isoFormat('D [de] MMMM');
        return [
            'estado'                   => 'vencido',
            'mensaje'                  => 'Vencido desde el '.$desde,
            'pendiente_extra'          => $tienePendienteExtra,
            'pendiente_extra_mensaje'  => $tienePendienteExtra ? 'Tienes un pago pendiente de revisión' : null,
        ];
    }
}
