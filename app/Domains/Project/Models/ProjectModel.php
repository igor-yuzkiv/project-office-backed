<?php

namespace App\Domains\Project\Models;

use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasArchivableColumns;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use App\Infrastructure\Models\Contracts\Archivable;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\TagFilter;
use App\Libs\EloquentFilters\Filters\TextFilter;
use App\Support\TextUtils;
use Database\Factories\ProjectModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property ProjectStatus $status
 * @property string|null $description
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property UserModel|null $archivedBy
 * @property Collection<int, TagModel> $tags
 * @property Collection<int, TaskModel> $tasks
 * @property Collection<int, TaskListModel> $taskLists
 * @property Collection<int, ProjectDocumentModel> $documents
 *
 * @method static \Illuminate\Database\Eloquent\Builder filter(array $filters)
 */
#[Fillable(['id', 'name', 'prefix', 'status', 'description', 'start_date', 'end_date', 'created_by', 'updated_by'])]
class ProjectModel extends Model implements Archivable
{
    /** @use HasFactory<ProjectModelFactory> */
    use HasArchivableColumns, HasAuditableColumns, HasFactory, HasFilters, HasUlids, Searchable;

    protected $table = 'projects';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'status'      => ProjectStatus::class,
            'start_date'  => 'date',
            'end_date'    => 'date',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProjectModel $project): void {
            if (trim((string) $project->prefix) !== '') {
                return;
            }

            $project->prefix = TextUtils::acronym((string) $project->name);
        });
    }

    public function wasStatusChangedToArchived(): bool
    {
        return $this->isDirty('status') && $this->status === ProjectStatus::ARCHIVED;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'archived_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'project_id');
    }

    public function taskLists(): HasMany
    {
        return $this->hasMany(TaskListModel::class, 'project_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocumentModel::class, 'project_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AttachmentModel::class, 'attachable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(TagModel::class, 'taggable', relatedPivotKey: 'tag_id')->withPivot('created_at');
    }

    public static function newFactory(): ProjectModelFactory
    {
        return ProjectModelFactory::new();
    }

    public function toSearchableArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'prefix' => $this->prefix,
            'status' => $this->status,
        ];
    }

    public static function allowedFilters(): array
    {
        return [
            new FilterDefinition(TextFilter::class, ['name', 'prefix', 'status']),
            new FilterDefinition(TagFilter::class, []),
        ];
    }
}
