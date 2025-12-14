<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah role user ada di dalam daftar $roles yang diizinkan
        if (! in_array($request->user()->role, $roles)) {
            // Jika tidak diizinkan, tendang dia
            abort(403, 'AKSES DITOLAK. ANDA TIDAK PUNYA IZIN.');
        }

        return $next($request);
    }
}