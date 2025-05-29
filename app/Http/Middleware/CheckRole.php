<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Array of allowed roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Jika pengguna tidak memiliki role yang diizinkan, tampilkan halaman 403
        // atau redirect ke halaman lain dengan pesan error.
        // Pastikan Anda memiliki view resources/views/errors/403.blade.php
        abort(403, 'ANDA TIDAK MEMILIKI AKSES UNTUK HALAMAN INI.'); 
    }
}