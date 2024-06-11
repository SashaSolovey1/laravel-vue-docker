<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        // Вставьте свой почтовый адрес
        Mail::to('solo160103@gmail.com')->send(new TestEmail());

        return 'Test email sent!';
    }
}
