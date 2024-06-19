<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run()
    {
        Comment::factory()->count(500)->create();

        // Обновляем некоторые комментарии, чтобы они стали ответами на другие
        $comments = Comment::all();
        foreach ($comments as $comment) {
            // 50% вероятность, что комментарий будет ответом на другой комментарий
            if (rand(0, 1) === 1) {
                // Найти случайный комментарий, id которого меньше текущего комментария
                $potentialParents = $comments->where('id', '<', $comment->id);

                if ($potentialParents->isNotEmpty()) {
                    $parentComment = $potentialParents->random();
                    $comment->parent_id = $parentComment->id;
                    $comment->save();
                }
            }
        }
    }
}
