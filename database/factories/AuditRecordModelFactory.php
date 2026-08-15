<?php

namespace Database\Factories;

use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<AuditRecordModel>
 */
class AuditRecordModelFactory extends Factory
{
    protected $model = AuditRecordModel::class;

    /**
     * Attaches a real entity, so $record->subject actually resolves.
     */
    public function forSubject(Model $subject): self
    {
        return $this->state([
            'subject_type' => $subject->getMorphClass(),
            'subject_id'   => (string) $subject->getKey(),
        ]);
    }

    public function definition(): array
    {
        return [
            'type'         => 'task.updated',
            'title'        => fake()->sentence(),
            'description'  => fake()->optional()->paragraph(),
            'subject_type' => null,
            'subject_id'   => null,
            'created_by'   => UserModel::factory(),
            'created_at'   => now(),
        ];
    }
}
