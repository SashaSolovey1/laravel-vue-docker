<?php

namespace App\Http\Controllers;


class CaptchaController extends Controller
{
    public function new() {
        // returns a new captcha image
        return captcha_img();
    }
}
