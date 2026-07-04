<?php

namespace App\Http\WebApi\Requests\Tasks;

use App\Domains\Task\Actions\SyncTaskOwners\SyncTaskOwnersCommand;
use App\Domains\Task\Actions\SyncTaskOwners\TaskOwnerItemDTO;
use App\Domains\Task\Enums\TaskOwnerRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncTaskOwnersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'owners'              => ['nullable', 'array'],
            'owners.*.user_id'    => ['required', 'string', 'exists:users,id', 'distinct'],
            'owners.*.role'       => ['nullable', 'string', Rule::enum(TaskOwnerRole::class)],
            'owners.*.is_primary' => ['required', 'boolean'],
        ];
    }

    public function toCommand(string $taskId): SyncTaskOwnersCommand
    {
        $owners = array_map(
            fn (array $owner) => new TaskOwnerItemDTO(
                userId: $owner['user_id'],
                role: isset($owner['role']) ? TaskOwnerRole::from($owner['role']) : null,
                isPrimary: (bool) $owner['is_primary'],
            ),
            $this->validated('owners') ?? [],
        );

        return new SyncTaskOwnersCommand(taskId: $taskId, owners: $owners);
    }
}
