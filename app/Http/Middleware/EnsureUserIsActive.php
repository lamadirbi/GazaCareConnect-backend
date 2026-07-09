<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->is_disabled) {
            $user->tokens()->delete();

            return response()->json(['message' => 'تم تعطيل هذا الحساب. تواصل مع الإدارة.'], 403);
        }

        return $next($request);
    }
}
