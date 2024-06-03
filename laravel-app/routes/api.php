<?php

use App\Http\Controllers\CaptchaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::resources([
    'comments' => CommentController::class,
]);

Route::post('comments/validate', [CommentController::class, 'validateComment']);


Route::prefix('comments')->group(function () {
    Route::put('/{comment}/increase-rating', [CommentController::class, 'increaseRating']);
    Route::put('/{comment}/decrease-rating', [CommentController::class, 'decreaseRating']);
});

Route::get('/comments/{id}', [CommentController::class, 'show']);


