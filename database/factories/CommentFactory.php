<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use App\Models\VideoPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        $commentableType = $this->faker->randomElement(['news', 'video_posts']);
        
        return [
            'content' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'commentable_type' => $commentableType === 'news' ? News::class : VideoPost::class,
            'commentable_id' => $commentableType === 'news' ? News::factory() : VideoPost::factory(),
        ];
    }

    public function reply(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => Comment::factory(),
                'commentable_type' => Comment::class,
                'commentable_id' => $attributes['parent_id'],
            ];
        });
    }
}