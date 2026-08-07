<?php

namespace App\Http\Controllers;

use App\Services\BrandingService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrandingAssetController extends Controller
{
    public function __construct(private readonly BrandingService $branding) {}

    public function logo(): StreamedResponse
    {
        $setting = $this->branding->currentSetting();

        abort_if($setting === null || blank($setting->logo_path), 404);

        $disk = Storage::disk($setting->logo_disk);
        abort_unless($disk->exists($setting->logo_path), 404);

        $stream = $disk->readStream($setting->logo_path);
        abort_if($stream === false, 404);

        $mimeType = $disk->mimeType($setting->logo_path) ?: 'application/octet-stream';
        $size = $disk->size($setting->logo_path);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, array_filter([
            'Cache-Control' => 'public, max-age=3600',
            'Content-Disposition' => 'inline; filename="branding-logo"',
            'Content-Length' => $size > 0 ? (string) $size : null,
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]));
    }
}
