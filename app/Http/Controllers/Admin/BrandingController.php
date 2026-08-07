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

        return view('admin.branding.index', [
            'branding' => $this->branding->current(),
            'versions' => OrganizationSetting::query()
                ->with('changedBy')
                ->orderByDesc('version')
                ->limit(12)
                ->get(),
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
}
