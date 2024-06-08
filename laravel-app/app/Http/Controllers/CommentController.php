<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Jobs\ProcessFile;
use Illuminate\Support\Facades\Cache;


class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sortBy', 'created_at');
        $sortDirection = $request->query('sortDirection', 'desc');

        // Создание уникального ключа кэша на основе параметров запроса
        $cacheKey = 'comments_' . $sortBy . '_' . $sortDirection . '_' . $request->query('page', 1);

        // Попытка получить данные из кэша
        $comments = Cache::remember($cacheKey, 600, function () use ($sortBy, $sortDirection) {
            $commentsQuery = Comment::with(['replies.user', 'user'])
                ->whereNull('parent_id');

            // Добавление сортировки
            if ($sortBy === 'username') {
                $commentsQuery->leftJoin('users', 'comments.user_id', '=', 'users.id')
                    ->orderBy('users.username', $sortDirection);
            } elseif ($sortBy === 'email') {
                $commentsQuery->leftJoin('users', 'comments.user_id', '=', 'users.id')
                    ->orderBy('users.email', $sortDirection);
            } else {
                $commentsQuery->orderBy($sortBy, $sortDirection);
            }

            // Получение комментариев с пагинацией
            $comments = $commentsQuery->paginate(25);

            // Преобразование коллекции комментариев
            $comments->getCollection()->transform(function ($comment) {
                $username = $comment->user ? $comment->user->username : 'Anonymous';
                $email = $comment->user ? $comment->user->email : '';

                // Трансформация ответов
                $replies = $comment->replies->map(function ($reply) {
                    $replyUsername = $reply->user ? $reply->user->username : 'Anonymous';
                    $replyEmail = $reply->user ? $reply->user->email : '';

                    return [
                        'id' => $reply->id,
                        'user_id' => $reply->user_id,
                        'username' => $replyUsername,
                        'email' => $replyEmail,
                        'parent_id' => $reply->parent_id,
                        'text' => $reply->text,
                        'rating' => $reply->rating,
                        'file_path' => $reply->file_path,
                        'created_at' => $reply->created_at,
                        'updated_at' => $reply->updated_at,
                    ];
                });

                return [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'username' => $username,
                    'email' => $email,
                    'parent_id' => $comment->parent_id,
                    'text' => $comment->text,
                    'rating' => $comment->rating,
                    'file_path' => $comment->file_path,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'replies' => $replies,
                ];
            });

            return $comments;
        });

        // Возврат JSON-ответа с пагинацией и комментариями
        return response()->json($comments);
    }



    public function validateComment(Request $request)
    {
            $request->captcha = intval($request->captcha);
            $rules = [
            'username' => 'required|string',
            'email' => 'required|email',
            'homepage' => 'nullable|url',
            'text' => 'required|string',
            'file' => 'sometimes|mimes:jpg,gif,png,txt|max:10240',
            'captcha' => 'required|captcha_api:' . request('key') . ',math',
        ];

        $validator = validator()->make(request()->all(), $rules);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        else {
            return $this->storeComment($request);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    private function storeComment(Request $request)
    {
        $user = User::firstOrCreate(
            ['email' => $request['email']],
            ['username' => $request['username']]
        );

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = 'comments/' . $fileName;
            $file->storeAs('comments', $fileName);
        }

        $comment = new Comment;
        $comment->user_id = $user->id;
        $comment->parent_id = $request->parent_id == 'null' ? null : $request->parent_id;
        $comment->home_page = $request->homepage;
        $comment->text = $request->text;
        $comment->rating = 0;
        $comment->file_path = $filePath;
        $comment->created_at = now();
        $comment->save();

        Cache::forget('comments_' . 'created_at' . '_desc' . '_1');

        event(new CommentCreated($comment));
        broadcast(new CommentCreated($comment))->toOthers();

        return response()->json("Comment saved", 201);
    }


    /**
     * Increase the rating of the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function increaseRating(Request $request, Comment $comment)
    {
        $comment->increment('rating');

        return response()->json($comment, 200);
    }

    /**
     * Decrease the rating of the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function decreaseRating(Request $request, Comment $comment)
    {
        $comment->decrement('rating');

        return response()->json($comment, 200);
    }

    public function show($id)
    {
        $comment = Comment::with('user')->findOrFail($id);

        $username = $comment->user ? $comment->user->username : 'Anonymous';
        $email = $comment->user ? $comment->user->email : '';

        $commentData = [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'username' => $username,
            'email' => $email,
            'parent_id' => $comment->parent_id,
            'text' => $comment->text,
            'rating' => $comment->rating,
            'file_path' => $comment->file_path,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
            'replies' => $comment->replies,
        ];

        return response()->json($commentData);
    }
}
