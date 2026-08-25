<?php

namespace App\Domains\ProjectDocument\Models;

use App\Domains\User\Models\UserModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_document_id
 * @property int $version_number
 * @property string|null $label
 * @property string|null $content
 * @property string|null $author_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ProjectDocumentModel $document
 * @property-read UserModel|null $author
 */
#[Fillable(['project_document_id', 'version_number', 'label', 'content', 'author_id'])]
class ProjectDocumentVersionModel extends Model
{
    use HasUlids;

    protected $table = 'project_document_versions';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectDocumentModel::class, 'project_document_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'author_id');
    }
}
