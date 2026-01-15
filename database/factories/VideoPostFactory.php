<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VideoPostFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence();
        
        return [
            'title' => $title,
            'description' => $this->faker->paragraphs(3, true),
            'video_url' => 'https://www.youtube.com/watch?v=' . Str::random(11),
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'duration' => $this->faker->numberBetween(60, 3600),
        ];
    }
}