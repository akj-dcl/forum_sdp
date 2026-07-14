<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Rate limit database writes: update presence at most once per minute
            if (!$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) >= 1) {
                $user->timestamps = false;
                $user->last_seen_at = now();
                $user->save();
            }
        }

        return $next($request);
    }
}
