<?php

namespace Database\Factories;

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
        return [
            'name' => fake()->words(2, true),
        ];
    }
}
