<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(): mixed
    {
        return view('admin.users.index', [
            'users' => User::query()->with('roles')->orderBy('name')->paginate(15),
        ]);
    }

    public function editReset(User $user): mixed
    {
        return view('admin.users.reset-password', ['user' => $user->load('roles')]);
    }

    public function resetPassword(ResetPasswordRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'resetPassword', $user, 'user.password_reset');

        $before = [
            'must_change_password' => $user->must_change_password,
            'password_changed_at' => $user->password_changed_at?->toIso8601String(),
        ];

        $user->forceFill([
            'password' => Hash::make($request->string('temporary_password')->toString()),
            'must_change_password' => true,
            'password_changed_at' => null,
            'remember_token' => null,
        ])->save();

        $this->auditLogger->succeeded(
            $actor,
            'user.password_reset',
            $user,
            'Password sementara dibuat; distribusikan melalui prosedur aman.',
            $before,
            ['must_change_password' => true, 'password_changed_at' => null],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Password {$user->name} berhasil direset. Sampaikan password sementara melalui prosedur aman.");
    }
}
