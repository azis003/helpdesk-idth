<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\DomainAuthorization;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly DomainAuthorization $authorization) {}

    public function index(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', AuditLog::class, 'admin.audit.view');

        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'outcome' => ['nullable', 'in:succeeded,denied'],
            'actor' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = AuditLog::query()->with('user')->latest('created_at');
        $query->when(filled($filters['action'] ?? null), fn ($builder) => $builder->where('action', 'like', '%'.trim($filters['action']).'%'));
        $query->when(filled($filters['outcome'] ?? null), fn ($builder) => $builder->where('outcome', $filters['outcome']));
        $query->when(filled($filters['actor'] ?? null), function ($builder) use ($filters): void {
            $actorFilter = trim((string) $filters['actor']);
            $builder->whereHas('user', fn ($userQuery) => $userQuery
                ->where('username', 'like', '%'.$actorFilter.'%')
                ->orWhere('name', 'like', '%'.$actorFilter.'%'));
        });
        $query->when(filled($filters['from'] ?? null), fn ($builder) => $builder->whereDate('created_at', '>=', $filters['from']));
        $query->when(filled($filters['to'] ?? null), fn ($builder) => $builder->whereDate('created_at', '<=', $filters['to']));

        return view('admin.audit-logs.index', [
            'auditLogs' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
        ]);
    }
}
