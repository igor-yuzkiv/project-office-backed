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
            'status'          => ProjectDocumentStatus::Draft->value,
        ];
    }

    public function withContent(?string $content = null): static
    {
        return $this->afterCreating(function (ProjectDocumentModel $document) use ($content): void {
            $document->versions()->create([
                'version_number' => 1,
                'content'        => $content ?? fake()->paragraphs(2, true),
            ]);
        });
    }
}
