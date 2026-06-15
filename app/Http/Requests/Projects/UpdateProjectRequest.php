<?php

namespace App\Http\Requests\Projects;

use App\Domains\Project\Actions\UpdateProject\UpdateProjectCommand;
use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Project\Models\ProjectModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'status'      => ['sometimes', Rule::enum(ProjectStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_date'  => ['sometimes', 'nullable', 'date'],
            'end_date'    => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'tag_ids'     => ['sometimes', 'array'],
            'tag_ids.*'   => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(ProjectModel $project): UpdateProjectCommand
    {
        $statusValue = $this->validated('status');
        $startDate = $this->validated('start_date');
        $endDate = $this->validated('end_date');

        return new UpdateProjectCommand(
            project: $project,
            name: $this->validated('name'),
            status: $statusValue ? ProjectStatus::from($statusValue) : null,
            description: $this->validated('description'),
            startDate: $startDate ? Carbon::parse($startDate) : null,
            endDate: $endDate ? Carbon::parse($endDate) : null,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
