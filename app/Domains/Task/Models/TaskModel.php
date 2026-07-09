<?php

namespace App\Domains\Task\Models;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use App\Infrastructure\Models\Contracts\Commentable;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\LookupFilter;
use App\Libs\EloquentFilters\Filters\TagFilter;
use App\Libs\EloquentFilters\Filters\TextFilter;
use Database\Factories\TaskModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property TaskPriority $priority
 * @property TaskStatus $status
 * @property Carbon|null $start_date
 * @property Carbon|null $due_date
 * @property Collection<int, TagModel> $tags
 * @property-read Collection<int, ProjectDocumentModel> $projectDocuments
 *
 * @method static \Illuminate\Database\Eloquent\Builder filter(array $filters)
 */
#[Fillable(['id', 'project_id', 'task_list_id', 'key', 'sequence_number', 'name', 'description', 'start_date', 'due_date', 'priority', 'status', 'created_by', 'updated_by'])]
class TaskModel extends Model implements Commentable
{
    /** @use HasFactory<TaskModelFactory> */
    use HasAuditableColumns, HasFactory, HasFilters, HasUlids, Searchable;

    protected $table = 'tasks';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date'   => 'date',
            'priority'   => TaskPriority::class,
            'status'     => TaskStatus::class,
        ];
    }

    /**
     * Allows route-model binding by ULID id or by the human-readable key (e.g. PROJ-123).
     *
     * @param  Builder<TaskModel>  $query
     * @return Builder<TaskModel>
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
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskListModel::class, 'task_list_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by');
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

    public function taskOwners(): HasMany
    {
        return $this->hasMany(TaskOwnerModel::class, 'task_id');
    }

    public function projectDocuments(): BelongsToMany
    {
        return $this->belongsToMany(ProjectDocumentModel::class, 'project_document_task', 'task_id', 'project_document_id')
            ->withTimestamps();
    }

    public static function newFactory(): TaskModelFactory
    {
        return TaskModelFactory::new();
    }

    public static function allowedFilters(): array
    {
        return [
            new FilterDefinition(TextFilter::class, ['name', 'description', 'key', 'status', 'priority']),
            new FilterDefinition(LookupFilter::class, ['project_id', 'task_list_id']),
            new FilterDefinition(TagFilter::class, []),
        ];
    }
}
