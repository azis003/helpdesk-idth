<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\RoleAssignmentHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoleAssignmentHistory>
 */
class RoleAssignmentHistoryFactory extends Factory
{
    protected $model = RoleAssignmentHistory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'role_id' => Role::factory(),
            'acted_by' => null,
            'action' => 'assigned',
            'reason' => null,
            'occurred_at' => now(),
        ];
    }
}
