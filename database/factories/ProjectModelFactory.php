<?php

namespace Database\Factories;

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;
use App\Support\TextUtils;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectModel>
 */
class ProjectModelFactory extends Factory
{
    protected $model = ProjectModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        $startDate = fake()->boolean(50) ? fake()->dateTimeBetween('-3 months', 'now') : null;
        $endDate = $startDate !== null && fake()->boolean(60) ? fake()->dateTimeBetween($startDate, '+6 months') : null;

        return [
            'name'        => $name,
            'prefix'      => TextUtils::acronym($name),
            'status'      => fake()->randomElement(ProjectStatus::cases())->value,
            'description' => fake()->boolean(60) ? fake()->paragraphs(2, true) : null,
            'start_date'  => $startDate !== null ? $startDate->format('Y-m-d') : null,
            'end_date'    => $endDate !== null ? $endDate->format('Y-m-d') : null,
        ];
    }
}
