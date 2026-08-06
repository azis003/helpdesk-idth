<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\Building;
use App\Models\Floor;
use App\Models\ProblemCategory;
use App\Models\Room;
use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkTeam;
use App\Policies\AnnouncementPolicy;
use App\Policies\AttachmentAccessPolicy;
use App\Policies\AttachmentPolicyPolicy;
use App\Policies\BuildingPolicy;
use App\Policies\FloorPolicy;
use App\Policies\ProblemCategoryPolicy;
use App\Policies\RoomPolicy;
use App\Policies\ServiceFieldDefinitionPolicy;
use App\Policies\ServiceTypePolicy;
use App\Policies\SkillPolicy;
use App\Policies\SlaPolicyPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkTeamPolicy;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(DomainAuthorization::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Attachment::class, AttachmentAccessPolicy::class);
        Gate::policy(AttachmentPolicy::class, AttachmentPolicyPolicy::class);
        Gate::policy(Building::class, BuildingPolicy::class);
        Gate::policy(Floor::class, FloorPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(ServiceFieldDefinition::class, ServiceFieldDefinitionPolicy::class);
        Gate::policy(ServiceType::class, ServiceTypePolicy::class);
        Gate::policy(SlaPolicy::class, SlaPolicyPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(WorkTeam::class, WorkTeamPolicy::class);
        Gate::policy(Skill::class, SkillPolicy::class);
        Gate::policy(ProblemCategory::class, ProblemCategoryPolicy::class);
    }
}
