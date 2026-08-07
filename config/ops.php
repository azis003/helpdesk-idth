<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operational monitoring
    |--------------------------------------------------------------------------
    |
    | These values are deliberately environment driven. Business settings
    | remain in the database, while deployment and infrastructure settings
    | belong to the environment that runs SIHATI.
    |
    */

    'storage_disk' => env('OPS_STORAGE_DISK', env('ATTACHMENT_PRIVATE_DISK', 'local')),
    'storage_warning_percent' => (int) env('OPS_STORAGE_WARNING_PERCENT', 80),
    'storage_critical_percent' => (int) env('OPS_STORAGE_CRITICAL_PERCENT', 90),
    'storage_alert_cooldown_minutes' => (int) env('OPS_STORAGE_ALERT_COOLDOWN_MINUTES', 60),
    'storage_alert_cache_prefix' => env('OPS_STORAGE_ALERT_CACHE_PREFIX', 'sihati:ops:storage:'),
    'log_channel' => env('OPS_LOG_CHANNEL', 'ops'),

    'scheduler_heartbeat_cache_key' => env(
        'OPS_SCHEDULER_HEARTBEAT_CACHE_KEY',
        'sihati:ops:scheduler-heartbeat',
    ),
    'scheduler_heartbeat_file' => env(
        'OPS_SCHEDULER_HEARTBEAT_FILE',
        storage_path('app/private/ops/scheduler-heartbeat.json'),
    ),

    'backup_status_file' => env(
        'OPS_BACKUP_STATUS_FILE',
        storage_path('app/private/ops/backup-status.json'),
    ),

    'health' => [
        'require_scheduler_heartbeat' => (bool) env('OPS_REQUIRE_SCHEDULER_HEARTBEAT', false),
        'require_backup_status' => (bool) env('OPS_REQUIRE_BACKUP_STATUS', false),
        'require_storage_check' => (bool) env('OPS_REQUIRE_STORAGE_CHECK', false),
        'fail_on_warning' => (bool) env('OPS_HEALTH_FAIL_ON_WARNING', false),
        'scheduler_max_age_seconds' => (int) env('OPS_SCHEDULER_MAX_AGE_SECONDS', 180),
        'backup_full_max_age_seconds' => (int) env('OPS_BACKUP_FULL_MAX_AGE_SECONDS', 90000),
        'backup_wal_max_age_seconds' => (int) env('OPS_BACKUP_WAL_MAX_AGE_SECONDS', 3600),
        'health_probe_cache_ttl_seconds' => (int) env('OPS_HEALTH_PROBE_CACHE_TTL_SECONDS', 10),
        'queue_max_depth' => (int) env('OPS_QUEUE_MAX_DEPTH', 100),
        'failed_jobs_warning_count' => (int) env('OPS_FAILED_JOBS_WARNING_COUNT', 0),
    ],
];
