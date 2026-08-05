<?php

namespace Tests;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * @param  list<RoleEnum|string>  $roles
     * @param  array<string, mixed>  $attributes
     */
    protected function createUser(array $roles = [], array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $roleSlugs = collect($roles)->map(fn (RoleEnum|string $role) => $role instanceof RoleEnum ? $role->value : $role);
        $roleIds = Role::query()->whereIn('slug', $roleSlugs)->pluck('id');
        $user->roles()->sync($roleIds);

        return $user->load('roles');
    }

    protected function passwordAttributes(string $password = 'Correct-Password-123!'): array
    {
        return [
            'password' => Hash::make($password),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ];
    }
}
