<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingService
{
    private ?Request $cachedRequest = null;

    /** @var array<string, mixed>|null */
    private ?array $cachedBranding = null;

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function currentSetting(): ?OrganizationSetting
    {
        return OrganizationSetting::query()
            ->active()
            ->with('changedBy')
            ->orderByDesc('version')
            ->first();
    }

    /** @return array{organization_name:string,application_name:string,tagline:?string,footer_text:?string,logo_path:?string,logo_disk:string,logo_url:?string,monogram:string,version:?int,effective_from:mixed,is_default:bool} */
    public function current(): array
    {
        $request = app()->bound('request') ? request() : null;

        if ($request !== null && $this->cachedBranding !== null && $this->cachedRequest === $request) {
            return $this->cachedBranding;
        }

        $setting = $this->currentSetting();
        $defaults = $this->defaults();
        $applicationName = (string) ($setting?->application_name ?? $defaults['application_name']);
        $logoUrl = $this->logoUrl($setting);

        $branding = [
            'organization_name' => (string) ($setting?->organization_name ?? $defaults['organization_name']),
            'application_name' => $applicationName,
            'tagline' => $setting?->tagline ?? $defaults['tagline'],
            'footer_text' => $setting?->footer_text ?? $defaults['footer_text'],
            'logo_path' => $setting?->logo_path,
            'logo_disk' => (string) ($setting?->logo_disk ?? $this->disk()),
            'logo_url' => $logoUrl,
            'monogram' => $this->monogram($applicationName),
            'version' => $setting?->version,
            'effective_from' => $setting?->effective_from,
            'is_default' => $setting === null,
        ];

        if ($request !== null) {
            $this->cachedRequest = $request;
            $this->cachedBranding = $branding;
        }

        return $branding;
    }

    /**
     * @param  array{organization_name:string,application_name:string,tagline:?string,footer_text:?string,remove_logo?:bool}  $data
     */
    public function update(User $actor, array $data, ?UploadedFile $logo = null): OrganizationSetting
    {
        $storedPath = null;
        $logoDisk = $this->disk();

        try {
            if ($logo !== null) {
                $storedPath = Storage::disk($logoDisk)->putFile('branding', $logo);

                if (! is_string($storedPath) || $storedPath === '') {
                    throw new \RuntimeException('Logo tidak dapat disimpan pada storage yang dipilih.');
                }
            }

            return $this->database->transaction(function () use ($actor, $data, $storedPath, $logoDisk): OrganizationSetting {
                $current = OrganizationSetting::query()
                    ->active()
                    ->lockForUpdate()
                    ->orderByDesc('version')
                    ->first();
                $before = $current === null ? null : $this->snapshot($current);

                $nextPath = $current?->logo_path;
                $nextDisk = $current?->logo_disk ?? $logoDisk;

                if ($storedPath !== null) {
                    $nextPath = $storedPath;
                    $nextDisk = $logoDisk;
                } elseif ((bool) ($data['remove_logo'] ?? false)) {
                    $nextPath = null;
                    $nextDisk = $logoDisk;
                }

                $nextValues = [
                    'organization_name' => trim((string) $data['organization_name']),
                    'application_name' => trim((string) $data['application_name']),
                    'tagline' => $this->nullableText($data['tagline'] ?? null),
                    'footer_text' => $this->nullableText($data['footer_text'] ?? null),
                    'logo_path' => $nextPath,
                    'logo_disk' => $nextDisk,
                ];

                if ($current !== null && $this->values($current) === $nextValues) {
                    return $current;
                }

                $version = ((int) OrganizationSetting::query()->lockForUpdate()->max('version')) + 1;

                if ($current !== null) {
                    $current->forceFill(['is_active' => false])->save();
                }

                $setting = OrganizationSetting::query()->create([
                    ...$nextValues,
                    'version' => $version,
                    'effective_from' => now(),
                    'is_active' => true,
                    'changed_by' => $actor->getKey(),
                ]);

                $this->auditLogger->succeeded(
                    $actor,
                    'admin.branding.updated',
                    $setting,
                    'Identitas dan branding aplikasi diperbarui; versi sebelumnya dipertahankan.',
                    $before,
                    $this->snapshot($setting),
                );

                return $setting->load('changedBy');
            });
        } catch (\Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk($logoDisk)->delete($storedPath);
            }

            throw $exception;
        }
    }

    /** @return array{organization_name:string,application_name:string,tagline:string,footer_text:string} */
    private function defaults(): array
    {
        $defaults = (array) config('branding.defaults', []);
        $applicationName = trim((string) ($defaults['application_name'] ?? ''));

        if ($applicationName === '' || strtolower($applicationName) === 'laravel') {
            $applicationName = 'SIHATI';
        }

        return [
            'organization_name' => trim((string) ($defaults['organization_name'] ?? $applicationName)) ?: $applicationName,
            'application_name' => $applicationName,
            'tagline' => trim((string) ($defaults['tagline'] ?? 'Portal Layanan TI')) ?: 'Portal Layanan TI',
            'footer_text' => trim((string) ($defaults['footer_text'] ?? 'Portal Layanan TI internal')) ?: 'Portal Layanan TI internal',
        ];
    }

    private function disk(): string
    {
        return (string) config('branding.disk', 'local');
    }

    private function logoUrl(?OrganizationSetting $setting): ?string
    {
        if ($setting?->logo_path === null || $setting->logo_path === '') {
            return null;
        }

        $disk = Storage::disk($setting->logo_disk);

        if (! $disk->exists($setting->logo_path)) {
            return null;
        }

        return route('branding.logo', ['v' => $setting->version]);
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function monogram(string $name): string
    {
        $words = preg_split('/[\s_-]+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) > 1) {
            return Str::upper(Str::substr(implode('', array_map(fn (string $word): string => Str::substr($word, 0, 1), array_slice($words, 0, 2))), 0, 2));
        }

        return Str::upper(Str::substr($name, 0, 2)) ?: 'SI';
    }

    /** @return array<string, mixed> */
    private function values(OrganizationSetting $setting): array
    {
        return [
            'organization_name' => $setting->organization_name,
            'application_name' => $setting->application_name,
            'tagline' => $setting->tagline,
            'footer_text' => $setting->footer_text,
            'logo_path' => $setting->logo_path,
            'logo_disk' => $setting->logo_disk,
        ];
    }

    /** @return array<string, mixed> */
    private function snapshot(OrganizationSetting $setting): array
    {
        return [
            ...$this->values($setting),
            'version' => $setting->version,
            'effective_from' => $setting->effective_from?->toIso8601String(),
            'is_active' => $setting->is_active,
            'changed_by' => $setting->changed_by,
        ];
    }
}
