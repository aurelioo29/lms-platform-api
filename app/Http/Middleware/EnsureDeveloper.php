<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class EnsureDeveloper
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== UserRole::Developer) {
            return response()->json([
                'message' => 'Forbidden. Developer access only.',
            ], 403);
        }

        return $next($request);
    }
}
