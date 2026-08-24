<?php

namespace App\Http\WebApi\Requests\Projects;

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
            'name'        => ['required', 'string', 'max:255'],
            'status'      => ['required', Rule::enum(ProjectStatus::class)],
            'icon'        => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'tag_ids'     => ['nullable', 'array'],
            'tag_ids.*'   => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(ProjectModel $project): UpdateProjectCommand
    {
        $startDate = $this->validated('start_date');
        $endDate = $this->validated('end_date');

        return new UpdateProjectCommand(
            project: $project,
            name: $this->validated('name'),
            icon: $this->validated('icon'),
            status: ProjectStatus::from($this->validated('status')),
            description: $this->validated('description'),
            startDate: $startDate ? Carbon::parse($startDate) : null,
            endDate: $endDate ? Carbon::parse($endDate) : null,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
