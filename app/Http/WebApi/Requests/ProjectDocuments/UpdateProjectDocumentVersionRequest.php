<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersion\UpdateProjectDocumentVersionCommand;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDocumentVersionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function toCommand(ProjectDocumentVersionModel $version): UpdateProjectDocumentVersionCommand
    {
        return new UpdateProjectDocumentVersionCommand(
            version: $version,
            label: $this->validated('label'),
        );
    }
}
