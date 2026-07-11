<?php

namespace App\Http\CliApi\Requests\Tasks;

use App\Domains\Task\Actions\CliAgentWorkflow\HandoffTask\HandoffTaskCommand;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Foundation\Http\FormRequest;

class HandoffTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'resolution' => ['required', 'string'],
        ];
    }

    public function toCommand(TaskModel $task): HandoffTaskCommand
    {
        /** @var UserModel $author */
        $author = $this->user();

        return new HandoffTaskCommand(
            task: $task,
            author: $author,
            resolution: $this->validated('resolution'),
        );
    }
}
