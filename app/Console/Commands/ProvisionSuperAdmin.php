<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProvisionSuperAdmin extends Command
{
    protected $signature = 'sihati:provision-super-admin {username} {name}';

    protected $description = 'Membuat akun Super Admin awal melalui prompt password aman.';

    public function handle(AuditLogger $auditLogger): int
    {
        $username = strtolower(trim((string) $this->argument('username')));
        $name = trim((string) $this->argument('name'));

        if (User::query()->where('username', $username)->exists()) {
            $this->components->error('Username tersebut sudah digunakan.');

            return self::FAILURE;
        }

        $password = $this->secret('Masukkan password awal');
        $confirmation = $this->secret('Konfirmasi password awal');

        $validation = Validator::make(
            [
                'username' => $username,
                'name' => $name,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'username' => ['required', 'string', 'max:80'],
                'name' => ['required', 'string', 'max:255'],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(12)->mixedCase()->numbers()->symbols(),
                ],
            ],
            [
                'username.required' => 'Username wajib diisi.',
                'name.required' => 'Nama wajib diisi.',
                'password.required' => 'Password awal wajib diisi.',
                'password.confirmed' => 'Konfirmasi password awal tidak sama.',
                'password.min' => 'Password awal minimal :min karakter.',
                'password.mixed' => 'Password awal harus mengandung huruf besar dan huruf kecil.',
                'password.numbers' => 'Password awal harus mengandung angka.',
                'password.symbols' => 'Password awal harus mengandung simbol.',
            ],
        );

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'username' => $username,
            'password' => Hash::make($password),
            'is_active' => true,
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $role = RoleModel::query()->firstOrCreate(
            ['slug' => Role::SuperAdmin->value],
            ['name' => Role::SuperAdmin->label()],
        );
        $user->roles()->attach($role->id, ['assigned_at' => now()]);

        $auditLogger->succeeded(
            $user,
            'user.super_admin_provisioned',
            $user,
            'Akun Super Admin awal dibuat melalui command provisioning.',
            null,
            ['role' => Role::SuperAdmin->value, 'must_change_password' => true],
        );

        $this->components->info('Akun Super Admin berhasil dibuat. Password wajib diganti saat login pertama.');

        return self::SUCCESS;
    }
}
