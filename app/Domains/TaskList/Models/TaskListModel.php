<?php

namespace App\Domains\TaskList\Models;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Enums\TaskListStatus;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use App\Infrastructure\Models\Contracts\Commentable;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\LookupFilter;
use App\Libs\EloquentFilters\Filters\TagFilter;
use App\Libs\EloquentFilters\Filters\TextFilter;
use Database\Factories\TaskListModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;

/**
 * @property string $id
 * @property string $project_id
 * @property string $key
 * @property int $sequence_number
 * @property string $name
 * @property TaskListStatus $status
 * @property string|null $description
 * @property-read ProjectModel $project
 * @property-read Collection<int, TaskModel> $tasks
 * @property-read Collection<int, TagModel> $tags
 * @property-read Collection<int, CommentModel> $comments
 * @property-read Collection<int, AttachmentModel> $attachments
 *
 * @method static \Illuminate\Database\Eloquent\Builder filter(array $filters)
 */
#[Fillable(['id', 'project_id', 'key', 'sequence_number', 'name', 'status', 'description', 'created_by', 'updated_by'])]
class TaskListModel extends Model implements Commentable
{
    /** @use HasFactory<TaskListModelFactory> */
    use HasAuditableColumns, HasFactory, HasFilters, HasUlids, Searchable;

    protected $table = 'task_lists';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'status' => TaskListStatus::class,
        ];
    }

    /**
     * Allows route-model binding by ULID id or by the human-readable key (e.g. PROJ-TL-1).
     *
     * @param  Builder<TaskListModel>  $query
     * @return Builder<TaskListModel>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where(function (Builder $q) use ($value): void {
            $q->where($this->getKeyName(), $value)->orWhere('key', $value);
        });
    }

    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'key'         => $this->key,
            'name'        => $this->name,
            'description' => $this->description,
            'project_id'  => $this->project_id,
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'task_list_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(TagModel::class, 'taggable', relatedPivotKey: 'tag_id')->withPivot('created_at');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(CommentModel::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AttachmentModel::class, 'attachable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by');
    }

    public static function newFactory(): TaskListModelFactory
    {
        return TaskListModelFactory::new();
    }

    public static function allowedFilters(): array
    {
        return [
            // project_id stays on TextFilter as well: the project page's task lists tab sends it
            // with filter_key 'text', and moving it would break that tab.
            new FilterDefinition(TextFilter::class, ['name', 'project_id', 'key', 'status']),
            new FilterDefinition(LookupFilter::class, ['project_id']),
            new FilterDefinition(TagFilter::class, []),
        ];
    }
}
