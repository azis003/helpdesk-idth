<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Branding storage and safe defaults
    |--------------------------------------------------------------------------
    |
    | Branding changes live in the database. These values are only the
    | deployment-safe fallback used before the first Super Admin update.
    |
    */

    'disk' => env('BRANDING_DISK', 'local'),

    'defaults' => [
        'organization_name' => env('APP_ORGANIZATION_NAME', 'SIHATI'),
        'application_name' => env('APP_NAME', 'SIHATI'),
        'tagline' => env('APP_TAGLINE', 'Portal Layanan TI'),
        'footer_text' => env('APP_FOOTER_TEXT', 'Portal Layanan TI internal'),
    ],
];
