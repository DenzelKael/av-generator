<?php

namespace Database\Factories;

use App\Models\MaterialMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialMovementFactory extends Factory
{
    protected $model = MaterialMovement::class;

    public function definition(): array
    {
        return [
            'movement_number' => fake()->unique()->numerify('48#####'),
            'type' => 'assignment',
            'has_correlation' => false,
            'source_file' => 'material-movements/example.csv',
        ];
    }
}
