<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_guest_can_see_indonesian_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('Masuk ke SIHATI')
            ->assertSee('Username')
            ->assertSee('Password');
    }

    public function test_guest_must_login_before_accessing_password_change_page(): void
    {
        $response = $this->get(route('password.change'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_user_can_login_and_logout_with_a_regenerated_session(): void
    {
        $user = $this->createUser([Role::Pemohon], array_merge(
            ['username' => 'pemohon.aktif'],
            $this->passwordAttributes('Correct-Password-123!'),
        ));

        $this->get(route('login'));

        $response = $this->post(route('login.store'), [
            'username' => 'PEMOHON.AKTIF',
            'password' => 'Correct-Password-123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login',
            'outcome' => 'succeeded',
        ]);

        $logout = $this->post(route('logout'));

        $logout->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.logout',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_initial_password_user_is_forced_to_change_password(): void
    {
        $user = $this->createUser([Role::Pemohon], array_merge(
            ['username' => 'pemohon.awal'],
            $this->passwordAttributes('Initial-Password-123!'),
            ['must_change_password' => true, 'password_changed_at' => null],
        ));

        $login = $this->post(route('login.store'), [
            'username' => $user->username,
            'password' => 'Initial-Password-123!',
        ]);

        $login->assertRedirect(route('dashboard'));

        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertOk()
            ->assertSee('Ganti password untuk melanjutkan')
            ->assertSee('Password awal wajib diganti sebelum Anda dapat menggunakan fitur SIHATI.')
            ->assertSee('aria-modal="true"', false);

        $this->get(route('password.change'))->assertRedirect(route('dashboard'));

        $change = $this->put(route('password.update'), [
            'current_password' => 'Initial-Password-123!',
            'password' => 'New-Password-456!',
            'password_confirmation' => 'New-Password-456!',
        ]);

        $change->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'must_change_password' => false,
        ]);
        $this->assertNotNull($user->fresh()->password_changed_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.initial_password_changed',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_invalid_credentials_are_generic_and_audited(): void
    {
        $this->createUser([Role::Pemohon], ['username' => 'pemohon.gagal']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'username' => 'pemohon.gagal',
            'password' => 'password-salah',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['username' => 'Username atau password tidak sesuai.']);
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'outcome' => 'denied',
            'reason' => 'Kredensial tidak valid atau akun tidak aktif.',
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->createUser([Role::Pemohon], array_merge(
            ['username' => 'pemohon.nonaktif', 'is_active' => false],
            $this->passwordAttributes(),
        ));

        $response = $this->post(route('login.store'), [
            'username' => $user->username,
            'password' => 'Correct-Password-123!',
        ]);

        $response->assertSessionHasErrors(['username' => 'Username atau password tidak sesuai.']);
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'outcome' => 'denied',
        ]);
    }
}
