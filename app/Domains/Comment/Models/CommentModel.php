<?php

namespace App\Domains\Comment\Models;

use App\Domains\User\Models\UserModel;
use Database\Factories\CommentModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property string $author_id
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserModel $author
 * @property-read Model $commentable
 */
#[Fillable(['commentable_id', 'commentable_type', 'author_id', 'content'])]
class CommentModel extends Model
{
    /** @use HasFactory<CommentModelFactory> */
    use HasFactory;

    // todo: use ulid

    protected $table = 'comments';

    public static function newFactory(): CommentModelFactory
    {
        return CommentModelFactory::new();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'author_id');
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
