<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isActive() && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        $this->auditLogger->denied(
            $user,
            'auth.role_required',
            null,
            'Peran yang diperlukan: '.implode(', ', $roles),
        );

        throw new AuthorizationException('Anda tidak memiliki peran yang diperlukan untuk membuka halaman ini.');
    }
}
