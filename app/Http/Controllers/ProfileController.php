<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function edit(Request $request): mixed
    {
        return view('profile.edit', [
            'user' => $request->user()->loadMissing('roles'),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $before = $user->only(['name', 'email']);

        $user->fill($request->validated());

        if (! $user->isDirty()) {
            return redirect()
                ->route('profile.edit')
                ->with('success', 'Tidak ada perubahan pada profil.');
        }

        $user->save();

        $this->auditLogger->succeeded(
            $user,
            'profile.updated',
            $user,
            null,
            $before,
            $user->only(['name', 'email']),
        );

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
