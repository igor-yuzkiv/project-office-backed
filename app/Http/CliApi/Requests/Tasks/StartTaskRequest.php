<?php

namespace App\Http\CliApi\Requests\Tasks;

use App\Domains\Task\Actions\CliAgentWorkflow\StartTask\StartTaskCommand;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Http\FormRequest;

class StartTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'limit'   => ['sometimes', 'integer', 'min:1', 'max:100'],
            'comment' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toCommand(TaskModel $task): StartTaskCommand
    {
        /** @var UserModel $author */
        $author = $this->user();

        return new StartTaskCommand(
            task: $task,
            author: $author,
            commentsLimit: $this->has('limit') ? $this->validated('limit') : 10,
            comment: $this->validated('comment'),
        );
    }
}
