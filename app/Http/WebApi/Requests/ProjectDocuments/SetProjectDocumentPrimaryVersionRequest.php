<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\Version\SetProjectDocumentPrimaryVersion\SetProjectDocumentPrimaryVersionCommand;
use App\Http\WebApi\Requests\Concerns\ResolvesRoutedProjectDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetProjectDocumentPrimaryVersionRequest extends FormRequest
{
    use ResolvesRoutedProjectDocument;

    public function rules(): array
    {
        return [
            // Null is the "use latest as primary" case, so the key has to be sent either way.
            'version_id' => [
                'present',
                'nullable',
                'string',
                'ulid',
                Rule::exists('project_document_versions', 'id')
                    ->where('project_document_id', $this->document()->id),
            ],
        ];
    }

    public function toCommand(): SetProjectDocumentPrimaryVersionCommand
    {
        return new SetProjectDocumentPrimaryVersionCommand(
            document: $this->document(),
            versionId: $this->validated('version_id'),
        );
    }
}
