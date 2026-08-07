<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', ServiceType::class, 'admin.catalog.view');
        $this->authorization->authorize($actor, 'viewAny', ServiceFieldDefinition::class, 'admin.catalog.fields.view');
        $this->authorization->authorize($actor, 'viewAny', Skill::class, 'admin.skills.view');
        $this->authorization->authorize($actor, 'viewAny', Building::class, 'admin.locations.view');
        $this->authorization->authorize($actor, 'viewAny', AttachmentPolicy::class, 'admin.attachment-policies.view');

        return view('admin.catalog.index', [
            'serviceTypes' => ServiceType::query()
                ->with(['variants', 'activeFieldDefinitions.options', 'skills'])
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
        ]);
    }

    public function updateService(ServiceTypeRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceType, 'admin.service_type.update');
        $this->catalog->updateServiceType($actor, $serviceType, $request->validated());

        return back()->with('success', "Layanan {$serviceType->code} berhasil diperbarui.");
    }

    public function updateServiceSkills(UpdateServiceSkillsRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceType, 'admin.service_type.skills.update');
        $this->catalog->syncServiceSkills($actor, $serviceType, $request->validated()['skill_ids'] ?? []);

        return back()->with('success', "Keahlian penanganan layanan {$serviceType->code} berhasil diperbarui.");
    }

    public function setServiceStatus(Request $request, ServiceType $serviceType, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceType, 'admin.service_type.status');
        $this->catalog->setServiceStatus($actor, $serviceType, $status === 'activate');

        return back()->with('success', $status === 'activate' ? "Layanan {$serviceType->code} berhasil diaktifkan." : "Layanan {$serviceType->code} dinonaktifkan.");
    }

    public function storeField(ServiceFieldRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', ServiceFieldDefinition::class, 'admin.service_field.create');
        $this->catalog->createField($actor, $serviceType, $request->payload());

        return back()->with('success', "Field baru untuk {$serviceType->code} berhasil dibuat.");
    }

    public function storeFieldVersion(ServiceFieldRequest $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.version');
        $this->catalog->createFieldVersion($actor, $serviceFieldDefinition, $request->payload());

        return back()->with('success', "Versi baru field {$serviceFieldDefinition->label} berhasil dibuat.");
    }

    public function activateField(Request $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.activate');
        $this->catalog->setFieldStatus($actor, $serviceFieldDefinition, true);

        return back()->with('success', 'Field formulir berhasil diaktifkan.');
    }

    public function deactivateField(Request $request, ServiceFieldDefinition $serviceFieldDefinition): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $serviceFieldDefinition, 'admin.service_field.deactivate');
        $this->catalog->setFieldStatus($actor, $serviceFieldDefinition, false);

        return back()->with('success', 'Field formulir dinonaktifkan. Definisi historis tetap tersedia.');
    }
}
