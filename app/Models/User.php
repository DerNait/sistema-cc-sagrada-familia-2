<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'apellido',
        'email',
        'password',
        'fecha_nacimiento',
        'fecha_registro',
        'rol_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role() {
        return $this->belongsTo(Role::class, 'rol_id');
    }       

    public function cursosAsignados()
    {
        return $this->belongsToMany(Curso::class, 'maestro_cursos', 'maestro_id', 'curso_id')
            ->withPivot('seccion_id')
            ->withTimestamps();
    }

    public function empleado() {
        return $this->hasOne(Empleado::class, 'usuario_id');
    } 

    public function estudiante() {
        return $this->hasOne(Estudiante::class, 'usuario_id');
    } 
}
