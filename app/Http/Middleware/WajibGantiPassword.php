<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->wajib_ganti_password) {
            return response()->json([
                'message' => 'Anda wajib mengganti password.',
                'wajib_ganti_password' => true,
            ], 403);
        }
        return $next($request);
    }
}