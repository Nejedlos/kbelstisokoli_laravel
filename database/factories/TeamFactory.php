<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;

        return [
            'name' => ['cs' => $name, 'en' => $name],
            'slug' => Str::slug($name),
            'category' => 'youth',
        ];
    }
}
