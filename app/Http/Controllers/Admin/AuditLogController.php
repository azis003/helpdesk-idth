<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(): mixed
    {
        return view('admin.audit-logs.index', [
            'auditLogs' => AuditLog::query()->with('user')->latest('created_at')->paginate(25),
        ]);
    }
}
