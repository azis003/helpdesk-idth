<?php

namespace Database\Factories;

use App\Models\WorkTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkTeam>
 */
class WorkTeamFactory extends Factory
{
    protected $model = WorkTeam::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Team',
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
