<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Ambil user dari guard yang sama dengan route: auth:api
        $user = auth()->guard('api')->user();

        // Kalau tidak ada user (token tidak ada / tidak valid)
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Super akses: role 'administrator' otomatis bisa mengakses semua route (termasuk route milik 'admin')
        if ($user->role === 'administrator') {
            return $next($request);
        }

        // Cek role untuk user selain administrator
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
