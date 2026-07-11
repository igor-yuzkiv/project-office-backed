<?php

namespace App\Http\CliApi\Requests\Tasks;

use App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask\CheckpointTaskCommand;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Http\FormRequest;

class CheckpointTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'comment' => ['required', 'string'],
        ];
    }

    public function toCommand(TaskModel $task): CheckpointTaskCommand
    {
        /** @var UserModel $author */
        $author = $this->user();

        return new CheckpointTaskCommand(
            task: $task,
            author: $author,
            subject: $this->validated('subject'),
            comment: $this->validated('comment'),
        );
    }
}
