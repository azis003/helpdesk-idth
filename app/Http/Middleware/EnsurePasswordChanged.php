<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null
            && $user->requiresPasswordChange()
            && ! $request->routeIs('password.change', 'password.update', 'logout')) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Ganti password awal Anda sebelum menggunakan fitur lain.');
        }

        return $next($request);
    }
}
