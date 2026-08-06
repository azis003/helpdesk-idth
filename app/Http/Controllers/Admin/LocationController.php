<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BuildingRequest;
use App\Http\Requests\Admin\FloorRequest;
use App\Http\Requests\Admin\RoomRequest;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Services\DomainAuthorization;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly LocationService $locations,
    ) {}

    public function storeBuilding(BuildingRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Building::class, 'admin.building.create');
        $this->locations->createBuilding($actor, $request->validated());

        return back()->with('success', 'Gedung berhasil dibuat.');
    }

    public function updateBuilding(BuildingRequest $request, Building $building): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $building, 'admin.building.update');
        $this->locations->updateBuilding($actor, $building, $request->validated());

        return back()->with('success', 'Gedung berhasil diperbarui.');
    }

    public function setBuildingStatus(Request $request, Building $building, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $building, 'admin.building.status');
        $this->locations->setBuildingStatus($actor, $building, $status === 'activate');

        return back()->with('success', $status === 'activate' ? 'Gedung berhasil diaktifkan.' : 'Gedung berhasil dinonaktifkan.');
    }

    public function storeFloor(FloorRequest $request, Building $building): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Floor::class, 'admin.floor.create');
        $this->locations->createFloor($actor, $building, $request->validated());

        return back()->with('success', 'Lantai berhasil dibuat.');
    }

    public function updateFloor(FloorRequest $request, Floor $floor): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $floor, 'admin.floor.update');
        $this->locations->updateFloor($actor, $floor, $request->validated());

        return back()->with('success', 'Lantai berhasil diperbarui.');
    }

    public function setFloorStatus(Request $request, Floor $floor, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $floor, 'admin.floor.status');
        $this->locations->setFloorStatus($actor, $floor, $status === 'activate');

        return back()->with('success', $status === 'activate' ? 'Lantai berhasil diaktifkan.' : 'Lantai berhasil dinonaktifkan.');
    }

    public function storeRoom(RoomRequest $request, Floor $floor): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Room::class, 'admin.room.create');
        $this->locations->createRoom($actor, $floor, $request->validated());

        return back()->with('success', 'Ruangan berhasil dibuat.');
    }

    public function updateRoom(RoomRequest $request, Room $room): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $room, 'admin.room.update');
        $this->locations->updateRoom($actor, $room, $request->validated());

        return back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function setRoomStatus(Request $request, Room $room, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $room, 'admin.room.status');
        $this->locations->setRoomStatus($actor, $room, $status === 'activate');

        return back()->with('success', $status === 'activate' ? 'Ruangan berhasil diaktifkan.' : 'Ruangan berhasil dinonaktifkan.');
    }
}
