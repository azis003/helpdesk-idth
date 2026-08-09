<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandingSettingRequest;
use App\Models\OrganizationSetting;
use App\Services\BrandingService;
use App\Services\DomainAuthorization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly BrandingService $branding,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeView($request);

        $versions = OrganizationSetting::query()
            ->with('changedBy')
            ->orderByDesc('version')
            ->limit(13)
            ->get();

        $versions->each(function (OrganizationSetting $version, int $index) use ($versions): void {
            $version->setAttribute(
                'changed_fields',
                $this->changedFields($version, $versions->get($index + 1)),
            );
        });

        return view('admin.branding.index', [
            'branding' => $this->branding->current(),
            'versions' => $versions->take(12),
        ]);
    }

    public function update(BrandingSettingRequest $request): RedirectResponse
    {
        $this->authorizeView($request);
        $setting = $this->branding->update($request->user(), $request->payload(), $request->file('logo'));

        return back()->with('success', "Identitas aplikasi berhasil disimpan sebagai versi {$setting->version}.");
    }

    private function authorizeView(Request $request): void
    {
        $this->authorization->authorize(
            $request->user(),
            'viewAny',
            OrganizationSetting::class,
            'admin.branding.view',
        );
    }

    /** @return list<string> */
    private function changedFields(OrganizationSetting $version, ?OrganizationSetting $previous): array
    {
        if ($previous === null) {
            return ['Identitas awal'];
        }

        $fields = [
            'Nama instansi' => ['organization_name'],
            'Nama aplikasi' => ['application_name'],
            'Tagline portal' => ['tagline'],
            'Teks halaman masuk' => ['footer_text'],
            'Logo' => ['logo_path', 'logo_disk'],
        ];
        $changed = [];

        foreach ($fields as $label => $attributes) {
            foreach ($attributes as $attribute) {
                if ($version->getAttribute($attribute) !== $previous->getAttribute($attribute)) {
                    $changed[] = $label;
                    break;
                }
            }
        }

        return $changed !== [] ? $changed : ['Tidak ada perubahan'];
    }
}
