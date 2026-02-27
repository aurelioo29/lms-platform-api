<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class EnsureAdminOrTeacherOrDeveloper
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // If your User model casts role to enum, $user->role is UserRole
        if (! in_array($user->role, [UserRole::Admin, UserRole::Teacher, UserRole::Developer], true)) {
            return response()->json([
                'message' => 'Forbidden. Admin/Teacher/Developer access only.',
            ], 403);
        }

        return $next($request);
    }
}
