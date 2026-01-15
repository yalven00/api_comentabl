<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence();
        
        return [
            'title' => $title,
            'description' => $this->faker->paragraphs(3, true),
            'slug' => Str::slug($title) . '-' . Str::random(5),
        ];
    }
}