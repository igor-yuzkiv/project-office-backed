<?php

namespace Database\Seeders;

use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = UserModel::factory(5)->create();
        $tags = TagModel::factory(100)->create();

        ProjectModel::factory(5)->create()->each(function (ProjectModel $project) use ($tags, $users): void {
            $project->tags()->attach(
                $tags->random(rand(0, 3))->pluck('id')->toArray()
            );

            TaskModel::factory(rand(10, 50))->create(['project_id' => $project->id])->each(function (TaskModel $task) use ($tags, $users): void {
                $task->tags()->attach(
                    $tags->random(rand(1, 10))->pluck('id')->toArray()
                );

                CommentModel::factory(rand(10, 50))->make()->each(function (CommentModel $comment) use ($task, $users): void {
                    $task->comments()->create([
                        'content'   => $comment->content,
                        'author_id' => $users->random()->id,
                    ]);
                });
            });
        });
    }
}
