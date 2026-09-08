<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
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
            'slug' => Str::slug($title),
            'content' => ['cs' => [], 'en' => []],
            'status' => 'published',
            'is_visible' => true,
        ];
    }
}
