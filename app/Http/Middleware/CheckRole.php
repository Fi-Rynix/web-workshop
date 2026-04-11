<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // idrole yang diizinkan (1=admin, 2=pelanggan)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Cek apakah user login
        if (!$user) {
            return redirect()->route('login');
        }

        // Cek apakah user memiliki role yang diizinkan
        if (!in_array($user->idrole, $roles)) {
            // Redirect berdasarkan role user
            if ($user->idrole == 1) {
                return redirect()->route('dashboard');
            } elseif ($user->idrole == 2) {
                return redirect()->route('pelanggan.dashboard');
            }

            // Jika role tidak dikenali, logout
            auth()->logout();
            return redirect()->route('login')->withErrors(['role' => 'Role tidak valid']);
        }

        return $next($request);
    }
}
