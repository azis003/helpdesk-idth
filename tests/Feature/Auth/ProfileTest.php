<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_header_shows_logged_in_user_role_and_account_menu(): void
    {
        $user = $this->createUser([Role::Pemohon], [
            'name' => 'Pengguna Header',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengguna Header')
            ->assertSee('Pemohon')
            ->assertSee('data-help-menu', false)
            ->assertSee('Bantuan')
            ->assertSee('data-account-menu', false)
            ->assertSee('Edit Profil')
            ->assertSee('Ganti Password')
            ->assertSee(route('password.change'))
            ->assertSee('Keluar');
    }

    public function test_user_can_update_their_own_profile_without_changing_account_identifiers(): void
    {
        $user = $this->createUser([Role::Pemohon], [
            'name' => 'Nama Lama',
            'username' => 'nama.lama',
            'email' => 'lama@example.test',
            'nip' => '198001012001011001',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.test',
            'username' => 'tidak-boleh-berubah',
            'nip' => '000000000000000000',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.test',
            'username' => 'nama.lama',
            'nip' => '198001012001011001',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'profile.updated',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_password_change_has_a_dedicated_page_separate_from_edit_profile(): void
    {
        $user = $this->createUser([Role::Pemohon]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Edit Profil')
            ->assertDontSee('Password baru');

        $this->actingAs($user)
            ->get(route('password.change'))
            ->assertOk()
            ->assertSee('Ganti Password')
            ->assertSee('Password baru');
    }
}
