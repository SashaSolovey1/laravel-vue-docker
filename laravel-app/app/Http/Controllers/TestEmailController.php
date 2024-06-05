<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        // Вставьте свой почтовый адрес
        Mail::to('solo160103@gmail.com')->send(new TestEmail());
        return 'Test email sent!';
    }
}
