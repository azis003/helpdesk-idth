<?php

namespace App\Providers;

use App\Models\ProblemCategory;
use App\Models\Skill;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkTeam;
use App\Policies\ProblemCategoryPolicy;
use App\Policies\SkillPolicy;
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
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(WorkTeam::class, WorkTeamPolicy::class);
        Gate::policy(Skill::class, SkillPolicy::class);
        Gate::policy(ProblemCategory::class, ProblemCategoryPolicy::class);
    }
}
