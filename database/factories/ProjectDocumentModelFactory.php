<?php

namespace Database\Factories;

use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectDocumentModel>
 */
class ProjectDocumentModelFactory extends Factory
{
    protected $model = ProjectDocumentModel::class;

    public function definition(): array
    {
        $sequence = fake()->unique()->numberBetween(1, 99999);

        return [
            'key'             => 'DOC-'.$sequence,
            'sequence_number' => $sequence,
            'title'           => fake()->unique()->words(3, true),
            'content'         => fake()->boolean(60) ? fake()->paragraphs(2, true) : null,
            'status'          => ProjectDocumentStatus::Draft->value,
        ];
    }
}
