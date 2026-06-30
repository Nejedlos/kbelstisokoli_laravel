<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'starts_at' => now()->addDays(rand(1, 30)),
            'ends_at' => now()->addDays(rand(1, 30))->addHour(),
            'location' => $this->faker->address,
            'notes' => $this->faker->sentence,
        ];
    }
}
