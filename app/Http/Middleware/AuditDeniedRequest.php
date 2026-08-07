<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditDeniedRequest
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            if ($this->shouldAudit($exception)
                && ! $request->attributes->get('sihati_denied_audited', false)) {
                $route = $request->route();
                $routeName = is_object($route) && is_string($route->getName())
                    ? $route->getName()
                    : null;

                $this->auditLogger->denied(
                    $request->user(),
                    $routeName !== null ? $routeName.'.denied' : 'request.denied',
                    $this->subject($route),
                    $this->reason($exception),
                    ['exception' => $exception::class],
                );
            }

            throw $exception;
        }
    }

    private function shouldAudit(Throwable $exception): bool
    {
        return $exception instanceof AuthorizationException
            || $exception instanceof ValidationException
            || ($exception instanceof HttpExceptionInterface
                && in_array($exception->getStatusCode(), [403, 404], true));
    }

    private function subject(mixed $route): ?Model
    {
        if (! is_object($route) || ! method_exists($route, 'parameters')) {
            return null;
        }

        foreach ($route->parameters() as $parameter) {
            if ($parameter instanceof Model) {
                return $parameter;
            }
        }

        return null;
    }

    private function reason(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return (string) (collect($exception->errors())->flatten()->first() ?: 'Validasi permintaan ditolak.');
        }

        if ($exception instanceof AuthorizationException) {
            return 'Otorisasi permintaan ditolak.';
        }

        if ($exception instanceof HttpExceptionInterface) {
            return 'Permintaan ditolak dengan status HTTP '.$exception->getStatusCode().'.';
        }

        return 'Permintaan ditolak.';
    }
}
