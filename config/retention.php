<?php

return [
    'ticket_years' => (int) env('RETENTION_TICKET_YEARS', 5),
    'data_export_days' => (int) env('RETENTION_DATA_EXPORT_DAYS', 90),
    'application_log_days' => (int) env('RETENTION_APPLICATION_LOG_DAYS', 30),
];
