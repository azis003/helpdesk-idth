<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original indexes are replaced with indexes that only enforce
     * uniqueness for users that have not been soft deleted.
     */
    private const ACTIVE_INDEXES = [
        'username' => 'users_username_active_unique',
        'email' => 'users_email_active_unique',
        'nip' => 'users_nip_active_unique',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_username_unique');
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_nip_unique');
        });

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite', 'sqlsrv'], true)) {
            foreach (self::ACTIVE_INDEXES as $column => $index) {
                DB::statement(sprintf(
                    'CREATE UNIQUE INDEX %s ON users (%s) WHERE deleted_at IS NULL',
                    $index,
                    $column,
                ));
            }

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('active_username', 80)
                    ->nullable()
                    ->storedAs('CASE WHEN deleted_at IS NULL THEN username ELSE NULL END');
                $table->string('active_email', 255)
                    ->nullable()
                    ->storedAs('CASE WHEN deleted_at IS NULL THEN email ELSE NULL END');
                $table->string('active_nip', 32)
                    ->nullable()
                    ->storedAs('CASE WHEN deleted_at IS NULL THEN nip ELSE NULL END');
            });

            Schema::table('users', function (Blueprint $table): void {
                $table->unique('active_username', self::ACTIVE_INDEXES['username']);
                $table->unique('active_email', self::ACTIVE_INDEXES['email']);
                $table->unique('active_nip', self::ACTIVE_INDEXES['nip']);
            });

            return;
        }

        throw new RuntimeException("Unsupported database driver [{$driver}] for soft-deleted user uniqueness.");
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach (self::ACTIVE_INDEXES as $index) {
            Schema::table('users', function (Blueprint $table) use ($index): void {
                $table->dropUnique($index);
            });
        }

        if (in_array($driver, ['pgsql', 'sqlite', 'sqlsrv'], true)) {
            // The filtered indexes above are regular database indexes, so the
            // dropUnique calls are sufficient for these drivers.
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn(['active_username', 'active_email', 'active_nip']);
            });
        } else {
            throw new RuntimeException("Unsupported database driver [{$driver}] for soft-deleted user uniqueness.");
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('username', 'users_username_unique');
            $table->unique('email', 'users_email_unique');
            $table->unique('nip', 'users_nip_unique');
        });
    }
};
