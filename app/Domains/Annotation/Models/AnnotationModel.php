<?php

namespace App\Domains\Annotation\Models;

use App\Domains\User\Models\UserModel;
use Database\Factories\AnnotationModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $annotatable_type
 * @property string $annotatable_id
 * @property string $author_id
 * @property string $content
 * @property array $anchor
 * @property string|null $text_snapshot
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserModel $author
 * @property-read Model $annotatable
 */
#[Fillable(['annotatable_id', 'annotatable_type', 'author_id', 'content', 'anchor', 'text_snapshot'])]
class AnnotationModel extends Model
{
    /** @use HasFactory<AnnotationModelFactory> */
    use HasFactory, HasUlids;

    protected $table = 'annotations';

    protected function casts(): array
    {
        return [
            'anchor' => 'array',
        ];
    }

    public static function newFactory(): AnnotationModelFactory
    {
        return AnnotationModelFactory::new();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'author_id');
    }

    public function annotatable(): MorphTo
    {
        return $this->morphTo();
    }
}
