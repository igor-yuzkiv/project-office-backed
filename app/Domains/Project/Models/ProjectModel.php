<?php

namespace App\Domains\Project\Models;

use App\Domains\Project\Enums\ProjectStatus;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
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
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;

/**
 * @property ProjectStatus $status
 * @property Collection<int, TagModel> $tags
 * @property Collection<int, TaskModel> $tasks
 * @property Collection<int, TaskListModel> $taskLists
 *
 * @method static \Illuminate\Database\Eloquent\Builder filter(array $filters)
 */
#[Fillable(['id', 'name', 'prefix', 'status', 'created_by', 'updated_by'])]
class ProjectModel extends Model
{
    /** @use HasFactory<ProjectModelFactory> */
    use HasAuditableColumns, HasFactory, HasFilters, HasUlids, Searchable;

    protected $table = 'projects';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'project_id');
    }

    public function taskLists(): HasMany
    {
        return $this->hasMany(TaskListModel::class, 'project_id');
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
