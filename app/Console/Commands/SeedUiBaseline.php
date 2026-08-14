<?php

namespace App\Console\Commands;

use Database\Seeders\UiBaselineFixtureSeeder;
use Illuminate\Console\Command;
use Throwable;

class SeedUiBaseline extends Command
{
    protected $signature = 'app:seed-ui-baseline
        {--current-approver=ACT-05 : Current approver profile: ACT-05 or ACT-09}';

    protected $description = 'Membuat deterministic UI baseline fixture pada database local/testing disposable.';

    public function handle(UiBaselineFixtureSeeder $seeder): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->components->error(
                'UI baseline fixture ditolak: command hanya boleh dijalankan pada environment local atau testing.',
            );

            return self::FAILURE;
        }

        $profile = strtoupper(trim((string) $this->option('current-approver')));

        try {
            $summary = $seeder->seed($profile);
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $state = $summary['existing'] ? 'sudah tersedia; tidak ada data ditulis ulang' : 'berhasil dibuat';
        $this->components->info("UI baseline fixture {$state}.");
        $this->table(
            ['Item', 'Nilai'],
            [
                ['Environment', app()->environment()],
                ['Current approver profile', $summary['approver_profile']],
                ['Current approver username', $summary['current_approver']],
                ['Actors', $summary['actors']],
                ['Work teams', $summary['teams']],
                ['Tickets', $summary['tickets']],
                ['Canonical statuses', $summary['statuses'].' / 11'],
                ['Canonical priorities', $summary['priorities'].' / 4'],
                ['Service codes', $summary['services'].' / 7'],
                ['Comments', $summary['comments']],
                ['Attachments (including retained soft delete)', $summary['attachments']],
            ],
        );
        $this->newLine();
        $this->line('Username actor menggunakan prefix: ui_test_');
        $this->line('Password local fixture: '.UiBaselineFixtureSeeder::DEFAULT_PASSWORD);
        $this->warn('Credential di atas hanya untuk environment local/testing dan tidak boleh digunakan di production.');

        return self::SUCCESS;
    }
}
