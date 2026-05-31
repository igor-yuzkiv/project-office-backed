<?php

namespace Database\Factories;

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

        return [
            'name'   => $name,
            'prefix' => TextUtils::acronym($name),
        ];
    }
}
