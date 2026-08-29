<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersionContent\UpdateProjectDocumentVersionContentCommand;
use App\Domains\ProjectDocument\Models\ProjectDocumentVersionModel;
use App\Http\WebApi\Requests\Concerns\ResolvesRoutedProjectDocument;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDocumentVersionContentRequest extends FormRequest
{
    use ResolvesRoutedProjectDocument;

    public function rules(): array
    {
        return [
            'content' => ['present', 'nullable', 'string'],
        ];
    }

    public function toCommand(ProjectDocumentVersionModel $version): UpdateProjectDocumentVersionContentCommand
    {
        return new UpdateProjectDocumentVersionContentCommand(
            document: $this->document(),
            version: $version,
            content: $this->validated('content'),
        );
    }
}
