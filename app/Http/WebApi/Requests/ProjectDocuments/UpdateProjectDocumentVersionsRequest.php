<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\Version\UpdateProjectDocumentVersions\UpdateProjectDocumentVersionsCommand;
use App\Http\WebApi\Requests\Concerns\ResolvesRoutedProjectDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectDocumentVersionsRequest extends FormRequest
{
    use ResolvesRoutedProjectDocument;

    public function rules(): array
    {
        return [
            'versions'      => ['required', 'array', 'min:1'],
            'versions.*.id' => [
                'required',
                'string',
                'ulid',
                'distinct',
                Rule::exists('project_document_versions', 'id')
                    ->where('project_document_id', $this->document()->id),
            ],
            'versions.*.content' => ['present', 'nullable', 'string'],
            'versions.*.label'   => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function toCommand(): UpdateProjectDocumentVersionsCommand
    {
        /** @var array<int, array{id: string, content: string|null, label: string|null}> $versions */
        $versions = $this->validated('versions');

        return new UpdateProjectDocumentVersionsCommand(
            document: $this->document(),
            versions: $versions,
        );
    }
}
