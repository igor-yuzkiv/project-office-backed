<?php

namespace Database\Seeders;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        ProjectModel::factory(15)->create()->each(function (ProjectModel $project): void {
            TaskModel::factory(10)->create(['project_id' => $project->id]);
        });
    }
}
