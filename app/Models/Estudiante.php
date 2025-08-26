<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Estudiante extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function beca()
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }
    
    public function secciones()
    {
        return $this->belongsToMany(
            Seccion::class,
            'seccion_estudiantes',   // ← plural
            'estudiante_id',
            'seccion_id'
        );
    }

    public function haPagado(int $diasVigencia = 30): bool
    {
        $ultimoPago = $this->pagos()->orderByDesc('aprobado_en')->first();

        if (!$ultimoPago) {
            return false; // nunca ha pagado
        }

        $fechaVencimiento = Carbon::parse($ultimoPago->aprobado_en)->addDays($diasVigencia);

        return $fechaVencimiento->isFuture(); // true si aún está vigente
    }
}
