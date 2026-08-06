<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AttachmentPolicyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ServiceCatalogController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WorkTeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/password/change', fn () => redirect()->route('dashboard'))->name('password.change');
    Route::put('/password/change', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('password.changed')->group(function () {
        Route::post('/tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('tickets.claim');
        Route::post('/tickets/{ticket}/handle', [TicketController::class, 'handle'])->name('tickets.handle');

        Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
            Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
            Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
            Route::put('/users/{user}/roles', [UserManagementController::class, 'updateRoles'])->name('users.roles.update');
            Route::put('/users/{user}/team', [UserManagementController::class, 'updateTeam'])->name('users.team.update');
            Route::delete('/users/{user}/team', [UserManagementController::class, 'removeTeam'])->name('users.team.remove');
            Route::put('/users/{user}/skills', [UserManagementController::class, 'updateSkills'])->name('users.skills.update');
            Route::get('/users/{user}/reset-password', [UserManagementController::class, 'editReset'])->name('users.reset-password.edit');
            Route::put('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');

            Route::get('/teams', [WorkTeamController::class, 'index'])->name('teams.index');
            Route::post('/teams', [WorkTeamController::class, 'store'])->name('teams.store');
            Route::put('/teams/{workTeam}', [WorkTeamController::class, 'update'])->name('teams.update');
            Route::post('/teams/{workTeam}/activate', [WorkTeamController::class, 'activate'])->name('teams.activate');
            Route::post('/teams/{workTeam}/deactivate', [WorkTeamController::class, 'deactivate'])->name('teams.deactivate');
            Route::delete('/teams/{workTeam}', [WorkTeamController::class, 'destroy'])->name('teams.destroy');
            Route::post('/teams/{workTeam}/members', [WorkTeamController::class, 'assignMember'])->name('teams.members.assign');
            Route::delete('/teams/{workTeam}/members/{user}', [WorkTeamController::class, 'removeMember'])->name('teams.members.remove');
            Route::put('/teams/{workTeam}/chair', [WorkTeamController::class, 'assignChair'])->name('teams.chair.update');

            Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
            Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
            Route::put('/skills/{skill}', [SkillController::class, 'update'])->name('skills.update');
            Route::post('/skills/{skill}/activate', [SkillController::class, 'activate'])->name('skills.activate');
            Route::post('/skills/{skill}/deactivate', [SkillController::class, 'deactivate'])->name('skills.deactivate');
            Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');
            Route::post('/categories', [SkillController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{problemCategory}', [SkillController::class, 'updateCategory'])->name('categories.update');
            Route::post('/categories/{problemCategory}/activate', [SkillController::class, 'activateCategory'])->name('categories.activate');
            Route::post('/categories/{problemCategory}/deactivate', [SkillController::class, 'deactivateCategory'])->name('categories.deactivate');
            Route::delete('/categories/{problemCategory}', [SkillController::class, 'destroyCategory'])->name('categories.destroy');
            Route::put('/categories/{problemCategory}/skills', [SkillController::class, 'updateCategorySkills'])->name('categories.skills.update');

            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

            Route::get('/catalog', [ServiceCatalogController::class, 'index'])->name('catalog.index');
            Route::put('/catalog/services/{serviceType}', [ServiceCatalogController::class, 'updateService'])->name('catalog.services.update');
            Route::post('/catalog/services/{serviceType}/{status}', [ServiceCatalogController::class, 'setServiceStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('catalog.services.status');
            Route::post('/catalog/services/{serviceType}/fields', [ServiceCatalogController::class, 'storeField'])->name('catalog.fields.store');
            Route::post('/catalog/fields/{serviceFieldDefinition}/versions', [ServiceCatalogController::class, 'storeFieldVersion'])->name('catalog.fields.versions.store');
            Route::post('/catalog/fields/{serviceFieldDefinition}/{status}', [ServiceCatalogController::class, 'setFieldStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('catalog.fields.status');

            Route::post('/catalog/buildings', [LocationController::class, 'storeBuilding'])->name('catalog.buildings.store');
            Route::put('/catalog/buildings/{building}', [LocationController::class, 'updateBuilding'])->name('catalog.buildings.update');
            Route::post('/catalog/buildings/{building}/{status}', [LocationController::class, 'setBuildingStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('catalog.buildings.status');
            Route::post('/catalog/buildings/{building}/floors', [LocationController::class, 'storeFloor'])->name('catalog.floors.store');
            Route::put('/catalog/floors/{floor}', [LocationController::class, 'updateFloor'])->name('catalog.floors.update');
            Route::post('/catalog/floors/{floor}/{status}', [LocationController::class, 'setFloorStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('catalog.floors.status');
            Route::post('/catalog/floors/{floor}/rooms', [LocationController::class, 'storeRoom'])->name('catalog.rooms.store');
            Route::put('/catalog/rooms/{room}', [LocationController::class, 'updateRoom'])->name('catalog.rooms.update');
            Route::post('/catalog/rooms/{room}/{status}', [LocationController::class, 'setRoomStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('catalog.rooms.status');

            Route::post('/catalog/attachment-policies', [AttachmentPolicyController::class, 'store'])->name('catalog.attachment-policies.store');
            Route::put('/catalog/attachment-policies/{attachmentPolicy}', [AttachmentPolicyController::class, 'update'])->name('catalog.attachment-policies.update');
            Route::post('/catalog/attachment-policies/{attachmentPolicy}/{status}', [AttachmentPolicyController::class, 'setStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('catalog.attachment-policies.status');
        });

        Route::prefix('admin')->name('admin.')->middleware('role:super_admin,agen_tier_1')->group(function () {
            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            Route::post('/announcements/{announcement}/{status}', [AnnouncementController::class, 'setStatus'])
                ->whereIn('status', ['activate', 'deactivate'])
                ->name('announcements.status');
        });
    });
});
