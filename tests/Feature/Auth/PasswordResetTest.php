<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_super_admin_can_reset_another_users_password_without_forcing_a_change(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], array_merge(
            ['username' => 'super.admin'],
            $this->passwordAttributes(),
        ));
        $target = $this->createUser([Role::Pemohon], array_merge(
            ['username' => 'target.user'],
            $this->passwordAttributes(),
        ));

        $response = $this->actingAs($admin)->put(route('admin.users.reset-password', $target), [
            'temporary_password' => 'Temporary-Password-789!',
            'temporary_password_confirmation' => 'Temporary-Password-789!',
            'confirm_reset' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');
        $target = $target->fresh();
        $this->assertFalse($target->must_change_password);
        $this->assertNotNull($target->password_changed_at);
        $this->assertTrue(Hash::check('Temporary-Password-789!', $target->password));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $target->id,
            'action' => 'user.password_reset',
            'outcome' => 'succeeded',
        ]);

        Auth::logout();

        $login = $this->post(route('login.store'), [
            'username' => $target->username,
            'password' => 'Temporary-Password-789!',
        ]);

        $login->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Ganti password untuk melanjutkan')
            ->assertDontSee('mandatory-password-modal', false);
    }

    public function test_super_admin_cannot_reset_own_password_through_reset_procedure(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], array_merge(
            ['username' => 'super.admin.self'],
            $this->passwordAttributes(),
        ));

        $response = $this->actingAs($admin)->put(route('admin.users.reset-password', $admin), [
            'temporary_password' => 'Temporary-Password-789!',
            'temporary_password_confirmation' => 'Temporary-Password-789!',
            'confirm_reset' => '1',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $admin->id,
            'action' => 'user.password_reset',
            'outcome' => 'denied',
        ]);
    }
}
