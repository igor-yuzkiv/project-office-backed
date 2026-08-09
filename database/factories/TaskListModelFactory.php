<?php

namespace Database\Factories;

use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskListModel>
 */
class TaskListModelFactory extends Factory
{
    protected $model = TaskListModel::class;

    public function definition(): array
    {
        $sequenceNumber = fake()->unique()->numberBetween(1, 99999);

        return [
            'key'             => 'PRJ-TL-'.$sequenceNumber,
            'sequence_number' => $sequenceNumber,
            'name'            => fake()->words(2, true),
            'status'          => TaskListStatus::Open->value,
            'description'     => null,
        ];
    }
}
