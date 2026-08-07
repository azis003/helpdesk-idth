<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Support\Collection;

class SkillSuggestionService
{
    /**
     * @return Collection<int, User>
     */
    public function eligibleTierTwoUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('roles.slug', Role::AgenTier2->value))
            ->with([
                'skills' => fn ($query) => $query->where('skills.is_active', true)->orderBy('skills.name'),
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, array{user_id:int,user_name:string,matching_skill_ids:list<int>,matching_skill_names:list<string>,match_count:int}>
     */
    public function forCategory(?ProblemCategory $category, ?Collection $users = null): Collection
    {
        if ($category === null) {
            return collect();
        }

        $category->loadMissing([
            'skills' => fn ($query) => $query->where('skills.is_active', true)->orderBy('skills.name'),
        ]);
        $skillIds = $category->skills->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if ($skillIds === []) {
            return collect();
        }

        return $this->forSkillIds($skillIds, $users);
    }

    /**
     * Use the service catalog as the primary source for skill matching.
     * The optional category is only a compatibility fallback for historical tickets.
     *
     * @return Collection<int, array{user_id:int,user_name:string,matching_skill_ids:list<int>,matching_skill_names:list<string>,match_count:int}>
     */
    public function forServiceType(
        ?ServiceType $serviceType,
        ?Collection $users = null,
        ?ProblemCategory $legacyCategory = null,
    ): Collection {
        $serviceType?->loadMissing([
            'skills' => fn ($query) => $query->where('skills.is_active', true)->orderBy('skills.name'),
        ]);
        $skillIds = $serviceType?->skills
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all() ?? [];

        if ($skillIds !== []) {
            return $this->forSkillIds($skillIds, $users);
        }

        return $this->forCategory($legacyCategory, $users);
    }

    /**
     * @param  list<int>  $skillIds
     * @return Collection<int, array{user_id:int,user_name:string,matching_skill_ids:list<int>,matching_skill_names:list<string>,match_count:int}>
     */
    private function forSkillIds(array $skillIds, ?Collection $users = null): Collection
    {
        $users ??= $this->eligibleTierTwoUsers();

        $suggestions = $users
            ->map(function (User $user) use ($skillIds): ?array {
                $matchingSkills = $user->skills->whereIn('id', $skillIds)->values();

                if ($matchingSkills->isEmpty()) {
                    return null;
                }

                return [
                    'user_id' => (int) $user->getKey(),
                    'user_name' => $user->name,
                    'matching_skill_ids' => $matchingSkills->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
                    'matching_skill_names' => $matchingSkills->pluck('name')->values()->all(),
                    'match_count' => $matchingSkills->count(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        usort($suggestions, function (array $left, array $right): int {
            $matchOrder = $right['match_count'] <=> $left['match_count'];

            return $matchOrder !== 0
                ? $matchOrder
                : strcasecmp($left['user_name'], $right['user_name']);
        });

        return collect($suggestions);
    }

    /**
     * @param  Collection<int, ProblemCategory>  $categories
     * @return array<string, list<array{user_id:int,user_name:string,matching_skill_ids:list<int>,matching_skill_names:list<string>,match_count:int}>>
     */
    public function forCategories(Collection $categories): array
    {
        $users = $this->eligibleTierTwoUsers();

        return $categories->mapWithKeys(function (ProblemCategory $category) use ($users): array {
            return [(string) $category->getKey() => $this->forCategory($category, $users)->all()];
        })->all();
    }
}
