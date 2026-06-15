<?php

namespace Database\Seeders;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tags = TagModel::factory(100)->create();

        ProjectModel::factory(50)->create()->each(function (ProjectModel $project) use ($tags): void {
            $project->tags()->attach(
                $tags->random(rand(0, 3))->pluck('id')->toArray()
            );

            TaskModel::factory(200)->create(['project_id' => $project->id])->each(function (TaskModel $task) use ($tags): void {
                $task->tags()->attach(
                    $tags->random(rand(0, 3))->pluck('id')->toArray()
                );
            });
        });
    }
}
