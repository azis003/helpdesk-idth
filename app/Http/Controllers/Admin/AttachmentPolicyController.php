<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachmentPolicyRequest;
use App\Models\AttachmentPolicy;
use App\Services\AttachmentPolicyService;
use App\Services\DomainAuthorization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttachmentPolicyController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AttachmentPolicyService $policies,
    ) {}

    public function store(AttachmentPolicyRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', AttachmentPolicy::class, 'admin.attachment_policy.create');
        $this->policies->create($actor, $request->payload());

        return back()->with('success', 'Kebijakan lampiran berhasil dibuat.');
    }

    public function update(AttachmentPolicyRequest $request, AttachmentPolicy $attachmentPolicy): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $attachmentPolicy, 'admin.attachment_policy.update');
        $this->policies->update($actor, $attachmentPolicy, $request->payload());

        return back()->with('success', 'Kebijakan lampiran berhasil diperbarui.');
    }

    public function setStatus(Request $request, AttachmentPolicy $attachmentPolicy, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $attachmentPolicy, 'admin.attachment_policy.status');
        $this->policies->setStatus($actor, $attachmentPolicy, $status === 'activate');

        return back()->with('success', $status === 'activate' ? 'Kebijakan lampiran diaktifkan.' : 'Kebijakan lampiran dinonaktifkan.');
    }
}
