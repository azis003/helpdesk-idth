<?php

namespace Database\Factories;

use App\Models\TeamChairAssignment;
use App\Models\User;
use App\Models\WorkTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamChairAssignment>
 */
class TeamChairAssignmentFactory extends Factory
{
    protected $model = TeamChairAssignment::class;

    public function definition(): array
    {
        return [
            'work_team_id' => WorkTeam::factory(),
            'user_id' => User::factory(),
            'assigned_by' => null,
            'started_at' => now(),
            'ended_at' => null,
            'is_active' => true,
        ];
    }
}
