<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class LocationService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $data */
    public function createBuilding(User $actor, array $data): Building
    {
        return $this->database->transaction(function () use ($actor, $data): Building {
            $building = Building::query()->create(['name' => trim($data['name']), 'is_active' => true]);
            $this->auditLogger->succeeded($actor, 'admin.building.created', $building, 'Gedung dibuat.', null, $this->buildingSnapshot($building));

            return $building;
        });
    }

    /** @param array<string, mixed> $data */
    public function updateBuilding(User $actor, Building $building, array $data): Building
    {
        return $this->database->transaction(function () use ($actor, $building, $data): Building {
            $before = $this->buildingSnapshot($building);
            $building->forceFill(['name' => trim($data['name'])])->save();
            $building = $building->fresh();
            $this->auditLogger->succeeded($actor, 'admin.building.updated', $building, 'Gedung diperbarui.', $before, $this->buildingSnapshot($building));

            return $building;
        });
    }

    public function setBuildingStatus(User $actor, Building $building, bool $active): Building
    {
        return $this->database->transaction(function () use ($actor, $building, $active): Building {
            if (! $active && $building->activeFloors()->exists()) {
                throw ValidationException::withMessages(['building' => 'Gedung yang masih memiliki lantai aktif tidak dapat dinonaktifkan. Nonaktifkan lantainya terlebih dahulu.']);
            }

            $before = $this->buildingSnapshot($building);
            $building->forceFill(['is_active' => $active])->save();
            $building = $building->fresh();
            $this->auditLogger->succeeded($actor, $active ? 'admin.building.activated' : 'admin.building.deactivated', $building, $active ? 'Gedung diaktifkan.' : 'Gedung dinonaktifkan.', $before, $this->buildingSnapshot($building));

            return $building;
        });
    }

    /** @param array<string, mixed> $data */
    public function createFloor(User $actor, Building $building, array $data): Floor
    {
        return $this->database->transaction(function () use ($actor, $building, $data): Floor {
            if (! $building->is_active) {
                throw ValidationException::withMessages(['building' => 'Lantai hanya dapat ditambahkan pada gedung aktif.']);
            }

            $floor = $building->floors()->create([
                'name' => trim($data['name']),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => true,
            ]);
            $floor->load('building');
            $this->auditLogger->succeeded($actor, 'admin.floor.created', $floor, 'Lantai dibuat.', null, $this->floorSnapshot($floor));

            return $floor;
        });
    }

    /** @param array<string, mixed> $data */
    public function updateFloor(User $actor, Floor $floor, array $data): Floor
    {
        return $this->database->transaction(function () use ($actor, $floor, $data): Floor {
            $floor->load('building');
            $before = $this->floorSnapshot($floor);
            $floor->forceFill([
                'name' => trim($data['name']),
                'sort_order' => (int) ($data['sort_order'] ?? $floor->sort_order),
            ])->save();
            $floor = $floor->fresh('building');
            $this->auditLogger->succeeded($actor, 'admin.floor.updated', $floor, 'Lantai diperbarui.', $before, $this->floorSnapshot($floor));

            return $floor;
        });
    }

    public function setFloorStatus(User $actor, Floor $floor, bool $active): Floor
    {
        return $this->database->transaction(function () use ($actor, $floor, $active): Floor {
            if (! $active && $floor->activeRooms()->exists()) {
                throw ValidationException::withMessages(['floor' => 'Lantai yang masih memiliki ruangan aktif tidak dapat dinonaktifkan. Nonaktifkan ruangannya terlebih dahulu.']);
            }

            $floor->load('building');
            $before = $this->floorSnapshot($floor);
            $floor->forceFill(['is_active' => $active])->save();
            $floor = $floor->fresh('building');
            $this->auditLogger->succeeded($actor, $active ? 'admin.floor.activated' : 'admin.floor.deactivated', $floor, $active ? 'Lantai diaktifkan.' : 'Lantai dinonaktifkan.', $before, $this->floorSnapshot($floor));

            return $floor;
        });
    }

    /** @param array<string, mixed> $data */
    public function createRoom(User $actor, Floor $floor, array $data): Room
    {
        return $this->database->transaction(function () use ($actor, $floor, $data): Room {
            $floor->load('building');

            if (! $floor->is_active || ! $floor->building?->is_active) {
                throw ValidationException::withMessages(['floor' => 'Ruangan hanya dapat ditambahkan pada lantai dan gedung aktif.']);
            }

            $room = $floor->rooms()->create(['name' => trim($data['name']), 'is_active' => true]);
            $room->load('floor.building');
            $this->auditLogger->succeeded($actor, 'admin.room.created', $room, 'Ruangan dibuat.', null, $this->roomSnapshot($room));

            return $room;
        });
    }

    /** @param array<string, mixed> $data */
    public function updateRoom(User $actor, Room $room, array $data): Room
    {
        return $this->database->transaction(function () use ($actor, $room, $data): Room {
            $room->load('floor.building');
            $before = $this->roomSnapshot($room);
            $room->forceFill(['name' => trim($data['name'])])->save();
            $room = $room->fresh('floor.building');
            $this->auditLogger->succeeded($actor, 'admin.room.updated', $room, 'Ruangan diperbarui.', $before, $this->roomSnapshot($room));

            return $room;
        });
    }

    public function setRoomStatus(User $actor, Room $room, bool $active): Room
    {
        return $this->database->transaction(function () use ($actor, $room, $active): Room {
            $room->load('floor.building');
            $before = $this->roomSnapshot($room);
            $room->forceFill(['is_active' => $active])->save();
            $room = $room->fresh('floor.building');
            $this->auditLogger->succeeded($actor, $active ? 'admin.room.activated' : 'admin.room.deactivated', $room, $active ? 'Ruangan diaktifkan.' : 'Ruangan dinonaktifkan.', $before, $this->roomSnapshot($room));

            return $room;
        });
    }

    /** @return array<string, mixed> */
    private function buildingSnapshot(Building $building): array
    {
        return ['id' => $building->id, 'name' => $building->name, 'is_active' => $building->is_active];
    }

    /** @return array<string, mixed> */
    private function floorSnapshot(Floor $floor): array
    {
        return ['id' => $floor->id, 'building' => $floor->building?->name, 'name' => $floor->name, 'sort_order' => $floor->sort_order, 'is_active' => $floor->is_active];
    }

    /** @return array<string, mixed> */
    private function roomSnapshot(Room $room): array
    {
        return ['id' => $room->id, 'building' => $room->floor?->building?->name, 'floor' => $room->floor?->name, 'name' => $room->name, 'is_active' => $room->is_active];
    }
}
