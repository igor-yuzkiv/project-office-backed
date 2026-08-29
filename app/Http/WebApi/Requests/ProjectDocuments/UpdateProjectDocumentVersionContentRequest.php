<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersionContent\UpdateProjectDocumentVersionContentCommand;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDocumentVersionContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => ['present', 'nullable', 'string'],
        ];
    }

    public function toCommand(ProjectDocumentVersionModel $version): UpdateProjectDocumentVersionContentCommand
    {
        return new UpdateProjectDocumentVersionContentCommand(
            version: $version,
            content: $this->validated('content'),
        );
    }
}
