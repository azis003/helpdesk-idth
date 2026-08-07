<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Notifications\OperationalAlertNotification;
use App\Services\StorageCapacityService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OperationalReadinessTest extends TestCase
{
    public function test_readiness_endpoint_reports_dependencies_without_internal_paths(): void
    {
        Config::set('ops.health.require_scheduler_heartbeat', false);
        Config::set('ops.health.require_backup_status', false);
        Config::set('ops.health.require_storage_check', false);

        $response = $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'ready',
                'checked_at',
                'checks' => ['database', 'cache', 'queue', 'storage', 'scheduler', 'backup'],
            ]);

        $this->assertTrue($response->json('ready'));
        $this->assertStringNotContainsString(storage_path(), $response->getContent());
    }

    public function test_readiness_fails_when_production_backup_and_scheduler_markers_are_required(): void
    {
        Config::set('ops.health.require_scheduler_heartbeat', true);
        Config::set('ops.health.require_backup_status', true);

        $this->getJson('/health/ready')
            ->assertStatus(503)
            ->assertJsonPath('ready', false)
            ->assertJsonPath('checks.scheduler.status', 'critical')
            ->assertJsonPath('checks.backup.status', 'critical');
    }

    public function test_scheduler_heartbeat_updates_cache_and_marker_file(): void
    {
        $path = storage_path('framework/testing/operational-heartbeat.json');
        Config::set('ops.scheduler_heartbeat_file', $path);

        $this->assertSame(0, Artisan::call('sihati:ops:heartbeat'));
        $this->assertNotNull(Cache::get(config('ops.scheduler_heartbeat_cache_key')));
        $this->assertFileExists($path);

        @unlink($path);
    }

    public function test_storage_warning_notifies_active_super_admin(): void
    {
        $administrator = $this->createUser([Role::SuperAdmin]);
        $inactiveAdministrator = $this->createUser([Role::SuperAdmin], ['is_active' => false]);
        $regularUser = $this->createUser([Role::Pemohon]);
        Config::set('ops.storage_alert_cache_prefix', 'test:sihati:storage:');

        app(StorageCapacityService::class)->alert([
            'disk' => 'local',
            'status' => 'warning',
            'message' => 'Storage privat mencapai ambang peringatan.',
            'used_percent' => 80.25,
            'free_bytes' => 100,
            'total_bytes' => 500,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => OperationalAlertNotification::class,
            'notifiable_type' => $administrator->getMorphClass(),
            'notifiable_id' => $administrator->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'type' => OperationalAlertNotification::class,
            'notifiable_id' => $inactiveAdministrator->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'type' => OperationalAlertNotification::class,
            'notifiable_id' => $regularUser->id,
        ]);
    }

    public function test_storage_capacity_inspection_reads_private_disk(): void
    {
        Config::set('ops.storage_disk', 'local');

        $snapshot = app(StorageCapacityService::class)->inspect();

        $this->assertSame('local', $snapshot['disk']);
        $this->assertSame('local', $snapshot['driver']);
        $this->assertContains($snapshot['status'], ['ok', 'warning', 'critical']);
        $this->assertNotNull($snapshot['total_bytes']);
    }
}
