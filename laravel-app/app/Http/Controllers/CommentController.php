<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Events\CommentRatingChanged;
use App\Models\Comment;
use App\Models\User;
use App\Rules\ValidHtmlTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
        $cacheKey = 'comments_'.$sortBy.'_'.$sortDirection.'_'.$request->query('page', 1);

        // Попытка получить данные из кэша
        $comments = Cache::remember($cacheKey, 600, function () use ($sortBy, $sortDirection) {
            $commentsQuery = Comment::with(['replies.user', 'user', 'media', 'replies.media'])
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
                        'file_path' => $reply->getFirstMediaUrl('images', 'thumb'),
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
                    'file_path' => $comment->getFirstMediaUrl('images', 'thumb'),
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
            'text' =>['required', new ValidHtmlTags()],
            'file' => 'sometimes|mimes:jpg,gif,png,txt|max:10240',
            'captcha' => 'required|captcha_api:'.request('key').',math',
        ];

        $messages = [
            'username.required' => 'Имя пользователя обязательно.',
            'email.required' => 'Электронная почта обязательна.',
            'email.email' => 'Неправильный формат электронной почты.',
            'homepage.url' => 'Домашняя страница должна быть валидным URL.',
            'text.required' => 'Текст обязателен.',
            'file.mimes' => 'Файл должен быть в формате: jpg, gif, png или txt.',
            'file.max' => 'Файл не должен превышать 10MB.',
            'captcha.required' => 'Капча обязательна.',
            'captcha.captcha_api' => 'Капча неверна, попробуйте обновить.',
        ];

        $validator = validator()->make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        } else {
            return $this->storeComment($request);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeComment(Request $request)
    {
        $user = User::firstOrCreate(
            ['email' => $request['email']],
            ['username' => $request['username']]
        );

        $comment = new Comment;
        $comment->user_id = $user->id;
        $comment->parent_id = $request->parent_id == 'null' ? null : $request->parent_id;
        $comment->home_page = $request->homepage;
        $comment->text = $request->text;
        $comment->rating = 0;
        $comment->created_at = now();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $allowedImageExtensions = ['jpg', 'gif', 'png'];
            $allowedTextExtensions = ['txt'];

            if (in_array($extension, $allowedImageExtensions)) {
                // Сохраняем изображение и уменьшаем его размер
                $comment->addMedia($file)
                    ->toMediaCollection('images');
            } elseif (in_array($extension, $allowedTextExtensions) && $file->getSize() <= 102400) {
                // Сохраняем текстовый файл
                $comment->addMedia($file)
                    ->toMediaCollection('files');
            } else {
                return response()->json('Invalid file type or size', 400);
            }
        }

        $comment->save();

        // Трансляция по вебсокету и отправка мейла
        event(new CommentCreated($comment));

        Cache::forget('comments_'.'created_at'.'_desc'.'_1');

        return response()->json('Comment saved', 201);
    }

    /**
     * Increase the rating of the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function increaseRating(Request $request, Comment $comment)
    {
        $comment->increment('rating');

        event(new CommentRatingChanged($comment));

        return response()->json($comment, 200);
    }

    /**
     * Decrease the rating of the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function decreaseRating(Request $request, Comment $comment)
    {
        $comment->decrement('rating');

        event(new CommentRatingChanged($comment));

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
