<?php

namespace App\Http\WebApi\Requests\ProjectDocuments;

use App\Domains\ProjectDocument\Actions\MoveProjectDocument\MoveProjectDocumentCommand;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveProjectDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var ProjectDocumentModel $projectDocument */
        $projectDocument = $this->route('project_document');

        return [
            'parent_id' => [
                'nullable',
                'string',
                'ulid',
                Rule::exists('project_documents', 'id')->where('project_id', $projectDocument->project_id),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('parent_id')) {
                return;
            }

            /** @var ProjectDocumentModel $document */
            $document = $this->route('project_document');
            $parentId = $this->input('parent_id');

            if ($parentId === $document->parent_id) {
                return;
            }

            if ($parentId === $document->id) {
                $validator->errors()->add('parent_id', 'A document cannot be its own parent.');

                return;
            }

            $newParent = $parentId !== null
                ? ProjectDocumentModel::query()->select(['id', 'path', 'depth'])->find($parentId)
                : null;

            if ($newParent !== null && in_array($document->id, explode('.', (string) $newParent->path), true)) {
                $validator->errors()->add('parent_id', 'A document cannot be moved under its own descendant.');

                return;
            }

            $maxNlevel = (int) ProjectDocumentModel::query()
                ->whereRaw('path <@ ?::ltree', [$document->path])
                ->selectRaw('MAX(nlevel(path)) as max_nlevel')
                ->value('max_nlevel');
            $oldNlevel = substr_count((string) $document->path, '.') + 1;
            $subtreeHeight = $maxNlevel - $oldNlevel;
            $newDepth = $newParent !== null ? $newParent->depth + 1 : 0;

            if ($newDepth + $subtreeHeight > ProjectDocumentModel::MAX_DEPTH) {
                $validator->errors()->add('parent_id', 'Maximum document nesting depth exceeded.');

                return;
            }

            $duplicateExists = ProjectDocumentModel::query()
                ->where('project_id', $document->project_id)
                ->where('id', '!=', $document->id)
                ->where('title', $document->title)
                ->when(
                    $parentId === null,
                    fn ($query) => $query->whereNull('parent_id'),
                    fn ($query) => $query->where('parent_id', $parentId),
                )
                ->exists();

            if ($duplicateExists) {
                $validator->errors()->add('parent_id', 'A document with the same title already exists under the target parent.');
            }
        });
    }

    public function toCommand(ProjectDocumentModel $projectDocument): MoveProjectDocumentCommand
    {
        return new MoveProjectDocumentCommand(
            document: $projectDocument,
            parentId: $this->validated('parent_id'),
        );
    }
}
