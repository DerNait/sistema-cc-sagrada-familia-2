<?php

namespace Database\Factories;

use App\Models\Beca;
use Illuminate\Database\Eloquent\Factories\Factory;

class BecaFactory extends Factory
{
    protected $model = Beca::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Básica', 'Parcial', 'Completa']),
            'descuento' => $this->faker->randomElement([10, 20, 30, 40, 50]),
        ];
    }
}