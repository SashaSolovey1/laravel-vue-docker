<?php

use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\TestEmailController;
use Illuminate\Support\Facades\Route;
use App\Events\CommentCreated;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/send-test-email', [TestEmailController::class, 'sendTestEmail']);

