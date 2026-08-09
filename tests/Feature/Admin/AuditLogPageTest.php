<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\User;
use Tests\TestCase;

class AuditLogPageTest extends TestCase
{
    public function test_super_admin_can_read_audit_trail_with_plain_language_and_filter_context(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], [
            'name' => 'Admin Utama',
            'username' => 'admin.utama',
        ]);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'admin.user.updated',
            'outcome' => 'succeeded',
            'auditable_type' => User::class,
            'auditable_id' => $admin->id,
            'reason' => 'Data pengguna diperbarui untuk kebutuhan pengujian.',
            'before' => ['is_active' => true],
            'after' => ['is_active' => false],
            'created_at' => now(),
        ]);

        AuditLog::query()->create([
            'action' => 'auth.login',
            'outcome' => 'denied',
            'reason' => 'Kredensial tidak valid.',
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', [
                'action' => 'admin.user.updated',
                'outcome' => 'succeeded',
            ]))
            ->assertOk()
            ->assertSee('Audit Trail')
            ->assertSee('Data pengguna diperbarui')
            ->assertSee('admin.user.updated')
            ->assertSee('Filter sedang digunakan.')
            ->assertSee('Berhasil')
            ->assertDontSee('Kembali ke pengguna');
    }

    public function test_non_super_admin_cannot_read_audit_trail(): void
    {
        $user = $this->createUser([Role::Pemohon]);

        $this->actingAs($user)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }
}
