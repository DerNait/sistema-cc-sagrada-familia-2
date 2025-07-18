<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\User;
use App\Models\Beca;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'beca_id'    => Beca::factory(),
        ];
    }
}
