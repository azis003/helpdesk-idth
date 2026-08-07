<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null
            && $user->requiresPasswordChange()
            && ! $request->routeIs('password.change', 'password.update', 'logout')) {
            $route = $request->route();
            $this->auditLogger->denied(
                $user,
                'auth.password_change_required',
                null,
                'Akses fitur ditolak sampai password awal diganti.',
                ['route' => is_object($route) && is_string($route->getName()) ? $route->getName() : null],
            );

            return redirect()
                ->route('dashboard')
                ->with('warning', 'Ganti password awal Anda sebelum menggunakan fitur lain.');
        }

        return $next($request);
    }
}
