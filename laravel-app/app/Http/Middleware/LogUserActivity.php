<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $userId = $request->user()->id;
            $duration = 300; // This represents 5 minutes

            Redis::setex("live_users:$userId", $duration, 1);
        }

        return $next($request);
    }
}
