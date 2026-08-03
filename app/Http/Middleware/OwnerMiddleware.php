<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || ($user->user_type !== 'owner' && $user->user_type !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux propriétaires.',
            ], 403);
        }

        return $next($request);
    }
}
