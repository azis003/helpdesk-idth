<?php

namespace App\Http\Controllers;

use App\Services\OperationalHealthService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function ready(OperationalHealthService $health): JsonResponse
    {
        $report = $health->check();
        $status = $report['ready'] ? 200 : 503;

        return response()->json([
            'status' => $report['status'],
            'ready' => $report['ready'],
            'checked_at' => $report['checked_at'],
            'checks' => collect($report['checks'])
                ->map(fn (array $check): array => [
                    'status' => $check['status'],
                    'message' => $check['message'],
                ])
                ->all(),
        ], $status, ['Cache-Control' => 'no-store']);
    }
}
