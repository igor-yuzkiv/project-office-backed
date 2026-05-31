<?php

namespace Database\Factories;

use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskModel>
 */
class TaskModelFactory extends Factory
{
    protected $model = TaskModel::class;

    public function definition(): array
    {
        $sequence = fake()->unique()->numberBetween(1, 99999);

        return [
            'key'             => 'TASK-'.$sequence,
            'sequence_number' => $sequence,
            'name'            => fake()->words(3, true),
            'description'     => null,
            'priority'        => TaskPriority::Medium->value,
            'status'          => TaskStatus::Open->value,
        ];
    }
}
