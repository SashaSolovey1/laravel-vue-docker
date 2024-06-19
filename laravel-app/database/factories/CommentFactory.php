<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'text' => $this->faker->paragraph(),
            'user_id' => User::inRandomOrder()->first()->id,
            'parent_id' => null,
            'rating' => $this->faker->numberBetween(-10, 10),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }
}
