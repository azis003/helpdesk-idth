<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateServiceTypeRequest;
use App\Http\Requests\Admin\ServiceFieldRequest;
use App\Http\Requests\Admin\ServiceTypeRequest;
use App\Http\Requests\Admin\UpdateServiceSkillsRequest;
use App\Models\AttachmentPolicy;
use App\Models\Building;
use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Services\DomainAuthorization;
use App\Services\ServiceCatalogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceCatalogController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly ServiceCatalogService $catalog,
    ) {}

    public function index(Request $request): mixed
    {
        $activeSection = $request->string('section')->toString();
        $activeSection = in_array($activeSection, ['services', 'forms', 'locations', 'attachments'], true)
            ? $activeSection
            : 'services';

        if ($activeSection === 'locations') {
            return app(LocationController::class)->index($request);
        }

        if ($activeSection === 'services') {
            return $this->services($request);
        }

        if ($activeSection === 'forms') {
            return $this->forms($request);
        }

        if ($activeSection === 'attachments') {
            return redirect()->route('admin.services.index');
        }

        $this->authorizeCatalog($request);

        return view('admin.catalog.index', [
            'activeSection' => $activeSection,
            ...$this->catalogData(),
        ]);
    }

    public function services(Request $request): mixed
    {
        $this->authorizeCatalog($request);

        $search = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $serviceTypes = $this->serviceTypeQuery()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $serviceQuery) use ($search): void {
                    $serviceQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.services.index', [
            'serviceTypes' => $serviceTypes,
            'search' => $search,
            'perPage' => $perPage,
            'serviceCount' => ServiceType::query()->count(),
            'activeServiceCount' => ServiceType::query()->where('is_active', true)->count(),
            'activeFieldCount' => ServiceFieldDefinition::query()->where('is_active', true)->count(),
            'skills' => Skill::query()->active()->orderBy('name')->get(),
            'fieldTypes' => ServiceCatalogService::FIELD_TYPES,
            'visibilities' => ServiceCatalogService::VISIBILITIES,
            'ticketClasses' => ServiceCatalogService::TICKET_CLASSES,
        ]);
    }

    public function forms(Request $request): RedirectResponse
    {
        $query = array_filter(['service' => $request->query('service')], fn ($value): bool => $value !== null && $value !== '');

        return redirect()->route('admin.services.index', $query);
    }

    private function authorizeCatalog(Request $request): void
    {
        $actor = $request->user();

        $this->authorization->authorize($actor, 'viewAny', ServiceType::class, 'admin.catalog.view');
        $this->authorization->authorize($actor, 'viewAny', ServiceFieldDefinition::class, 'admin.catalog.fields.view');
        $this->authorization->authorize($actor, 'viewAny', Skill::class, 'admin.skills.view');
        $this->authorization->authorize($actor, 'viewAny', Building::class, 'admin.locations.view');
        $this->authorization->authorize($actor, 'viewAny', AttachmentPolicy::class, 'admin.attachment-policies.view');
    }

    private function serviceTypeQuery(): Builder
    {
        return ServiceType::query()->with(['activeFieldDefinitions.options', 'activeSlaPolicy', 'skills']);
    }

    /** @return array<string, mixed> */
    private function catalogData(): array
    {
        return [
            'serviceTypes' => $this->serviceTypeQuery()
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get(),
            'skills' => Skill::query()->active()->orderBy('name')->get(),
            'buildings' => Building::query()
                ->with(['floors.rooms'])
                ->orderBy('name')
                ->get(),
            'attachmentPolicies' => AttachmentPolicy::query()
                ->with('serviceType')
                ->orderByRaw('service_type_id IS NOT NULL')
                ->orderBy('type_key')
                ->get(),
            'fieldTypes' => ServiceCatalogService::FIELD_TYPES,
            'visibilities' => ServiceCatalogService::VISIBILITIES,
            'ticketClasses' => ServiceCatalogService::TICKET_CLASSES,
        ];
    }

    public function storeService(CreateServiceTypeRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', ServiceType::class, 'admin.service_type.create');
        $serviceType = $this->catalog->createServiceType($actor, $request->payload());

        return redirect()
            ->route('admin.services.index', ['service' => $serviceType->id])
            ->with('success', "Layanan {$serviceType->code} berhasil dibuat.");
    }

    public function updateService(ServiceTypeRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceType, 'admin.service_type.update');
        $this->catalog->updateServiceType($actor, $serviceType, $request->validated());

        return $this->redirectToServiceEditor($request, $serviceType)
            ->with('success', "Layanan {$serviceType->code} berhasil diperbarui.");
    }

    public function updateServiceSkills(UpdateServiceSkillsRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceType, 'admin.service_type.skills.update');
        $this->catalog->syncServiceSkills($actor, $serviceType, $request->validated()['skill_ids'] ?? []);

        return $this->redirectToServiceEditor($request, $serviceType, 'skills')
            ->with('success', "Keahlian penanganan layanan {$serviceType->code} berhasil diperbarui.");
    }

    public function setServiceStatus(Request $request, ServiceType $serviceType, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceType, 'admin.service_type.status');
        $this->catalog->setServiceStatus($actor, $serviceType, $status === 'activate');

        return $this->redirectToServiceEditor($request, $serviceType)
            ->with('success', $status === 'activate' ? "Layanan {$serviceType->code} berhasil diaktifkan." : "Layanan {$serviceType->code} dinonaktifkan.");
    }

    public function storeField(ServiceFieldRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', ServiceFieldDefinition::class, 'admin.service_field.create');
        $this->catalog->createField($actor, $serviceType, $request->payload());

        return $this->redirectToServiceEditor($request, $serviceType, 'formulir')
            ->with('success', "Field baru untuk {$serviceType->code} berhasil dibuat.");
    }

    public function storeFieldVersion(ServiceFieldRequest $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.version');
        $this->catalog->createFieldVersion($actor, $serviceFieldDefinition, $request->payload());
        $serviceType = $serviceFieldDefinition->serviceType;

        return $this->redirectToServiceEditor($request, $serviceType, 'formulir')
            ->with('success', "Versi baru field {$serviceFieldDefinition->label} berhasil dibuat.");
    }

    public function activateField(Request $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.activate');
        $this->catalog->setFieldStatus($actor, $serviceFieldDefinition, true);
        $serviceType = $serviceFieldDefinition->serviceType;

        return $this->redirectToServiceEditor($request, $serviceType, 'formulir')
            ->with('success', 'Field formulir berhasil diaktifkan.');
    }

    public function deactivateField(Request $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.deactivate');
        $this->catalog->setFieldStatus($actor, $serviceFieldDefinition, false);
        $serviceType = $serviceFieldDefinition->serviceType;

        return $this->redirectToServiceEditor($request, $serviceType, 'formulir')
            ->with('success', 'Field formulir dinonaktifkan. Definisi historis tetap tersedia.');
    }

    public function removeField(Request $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.deactivate');
        $this->catalog->setFieldStatus($actor, $serviceFieldDefinition, false);
        $serviceType = $serviceFieldDefinition->serviceType;

        return $this->redirectToServiceEditor($request, $serviceType, 'formulir')
            ->with('success', "Field {$serviceFieldDefinition->label} dihapus dari formulir. Histori tiket tetap aman.");
    }

    private function redirectToServiceEditor(Request $request, ServiceType $serviceType, string $defaultTab = 'detail'): RedirectResponse
    {
        $requestedTab = $request->string('_service_tab')->toString();
        $tab = in_array($requestedTab, ['detail', 'skills', 'formulir'], true)
            ? $requestedTab
            : $defaultTab;
        $query = [];
        $referer = $request->headers->get('referer');

        if (is_string($referer) && $referer !== '') {
            $refererQuery = parse_url($referer, PHP_URL_QUERY);
            $refererParameters = [];

            if (is_string($refererQuery)) {
                parse_str($refererQuery, $refererParameters);
            }

            foreach (['q', 'per_page', 'page'] as $parameter) {
                if (isset($refererParameters[$parameter]) && is_scalar($refererParameters[$parameter])) {
                    $query[$parameter] = $refererParameters[$parameter];
                }
            }
        }

        return redirect()->route('admin.services.index', [
            ...$query,
            'service' => $serviceType->getKey(),
            'service_tab' => $tab,
        ]);
    }
}
