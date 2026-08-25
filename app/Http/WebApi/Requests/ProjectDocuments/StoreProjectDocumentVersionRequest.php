<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\CreateProjectDocumentVersion\CreateProjectDocumentVersionCommand;
use App\Http\WebApi\Requests\Concerns\ResolvesRoutedProjectDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDocumentVersionRequest extends FormRequest
{
    use ResolvesRoutedProjectDocument;

    public function rules(): array
    {
        return [
            'label'                        => ['nullable', 'string', 'max:255'],
            'copy_content_from_version_id' => [
                'nullable',
                'string',
                'ulid',
                Rule::exists('project_document_versions', 'id')
                    ->where('project_document_id', $this->document()->id),
            ],
        ];
    }

    public function toCommand(): CreateProjectDocumentVersionCommand
    {
        return new CreateProjectDocumentVersionCommand(
            document: $this->document(),
            label: $this->validated('label'),
            copyContentFromVersionId: $this->validated('copy_content_from_version_id'),
            authorId: $this->user()?->getAuthIdentifier(),
        );
    }
}
