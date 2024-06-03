<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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

        // Возврат JSON-ответа с пагинацией и комментариями
        return response()->json($comments);
    }



    public function validateComment(Request $request)
    {

        $rules = [
            'captcha' => 'required|captcha_api:' . request('key') . ',math',
            'username' => 'required|string',
            'email' => 'required|email',
            'homepage' => 'nullable|url',
            'text' => 'required|string',
            'file' => 'nullable|file|max:10240', // 10MB max size
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
        // Check if user with the given email exists
        $user = User::where('email', $request['email'])->first();

        // If user does not exist, create a new one
        if (!$user) {
            $user = new User;
            $user->username = $request['username'];
            $user->email = $request['email'];
            $user->save();
        }

        // Handle file upload if present
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('uploads/comments');
        }

        // Create the comment
        $comment = new Comment;
        $comment->user_id = $user->id;
        if($request['parent_id']):
        $comment->parent_id = $request['parent_id'];
        endif;
        $comment->home_page = $request['homepage'];
        $comment->text = $request['text'];
        $comment->rating = 0;
        $comment->file_path = $filePath;
        $comment->created_at = now();
        $comment->updated_at = null;
        $comment->save();

        // Log the created comment
        info('Created comment:', $comment->toArray());

        return response()->json($comment, 201);
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
