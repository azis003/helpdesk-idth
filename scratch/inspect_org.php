<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$settings = \App\Models\OrganizationSetting::first();
if ($settings) {
    echo "LOGO PATH: " . $settings->logo_path . "\n";
    echo "LOGO URL: " . $settings->logo_url . "\n";
    echo "APPLICATION NAME: " . $settings->application_name . "\n";
    echo "ORGANIZATION NAME: " . $settings->organization_name . "\n";
    if ($settings->logo_path) {
        $disk = \Storage::disk($settings->logo_disk);
        if ($disk->exists($settings->logo_path)) {
            echo "FILE EXISTS!\n";
            echo "SIZE: " . $disk->size($settings->logo_path) . " bytes\n";
            echo "FULL PATH: " . $disk->path($settings->logo_path) . "\n";
        } else {
            echo "FILE DOES NOT EXIST IN STORAGE!\n";
        }
    }
} else {
    echo "NO ORGANIZATION SETTING FOUND!\n";
}
