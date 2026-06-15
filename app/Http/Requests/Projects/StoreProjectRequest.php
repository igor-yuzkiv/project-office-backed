<?php

namespace App\Http\Requests\Projects;

use App\Domains\Project\Actions\CreateProject\CreateProjectCommand;
use App\Domains\Project\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'min:3'],
            'prefix'      => ['sometimes', 'string', 'max:5'],
            'status'      => ['sometimes', Rule::enum(ProjectStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_date'  => ['sometimes', 'nullable', 'date'],
            'end_date'    => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'tag_ids'     => ['sometimes', 'array'],
            'tag_ids.*'   => ['string', 'exists:tags,id'],
        ];
    }

    public function toCommand(): CreateProjectCommand
    {
        $statusValue = $this->validated('status');
        $startDate = $this->validated('start_date');
        $endDate = $this->validated('end_date');

        return new CreateProjectCommand(
            name: $this->validated('name'),
            prefix: $this->validated('prefix'),
            status: $statusValue ? ProjectStatus::from($statusValue) : ProjectStatus::DRAFT,
            description: $this->validated('description'),
            startDate: $startDate ? Carbon::parse($startDate) : null,
            endDate: $endDate ? Carbon::parse($endDate) : null,
            tagIds: $this->validated('tag_ids'),
        );
    }
}
