<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        return [
            'title' => ['cs' => $title, 'en' => $title],
            'slug' => \Illuminate\Support\Str::slug($title),
            'content' => ['cs' => [], 'en' => []],
            'status' => 'published',
            'is_visible' => true,
        ];
    }
}
