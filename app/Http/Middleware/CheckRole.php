<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     *
     * Uso en rutas:
     *   ->middleware('role:admin')           // solo administradores
     *   ->middleware('role:admin,worker')    // admin o trabajador
     *
     * @param  string  ...$roles  Roles permitidos: admin | worker
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Mapa de alias → role_id
        $roleMap = ['admin' => 1, 'worker' => 2];
        $allowedIds = array_map(fn ($r) => $roleMap[$r] ?? (int) $r, $roles);

        // Verifica cuenta activa
        if (! $user->activo) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.']);
        }

        // Verifica bloqueo temporal
        if ($user->blocked_until && $user->blocked_until->isFuture()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está temporalmente bloqueada. Intenta más tarde.']);
        }

        // Verifica rol
        if (! in_array($user->id_rol, $allowedIds, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
