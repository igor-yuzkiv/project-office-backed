<?php

namespace App\Domains\ProjectDocument\Models;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasArchivableColumns;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use App\Infrastructure\Models\Contracts\Archivable;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\TagFilter;
use App\Libs\EloquentFilters\Filters\TaskFilter;
use Database\Factories\ProjectDocumentModelFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string|null $parent_id
 * @property string $key
 * @property int $sequence_number
 * @property string $title
 * @property string|null $content
 * @property ProjectDocumentStatus $status
 * @property string $path
 * @property int $depth
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property-read ProjectModel $project
 * @property-read ProjectDocumentModel|null $parent
 * @property-read Collection<int, ProjectDocumentModel> $children
 * @property-read Collection<int, TaskModel> $tasks
 * @property-read Collection<int, TagModel> $tags
 * @property-read UserModel|null $archivedBy
 */
#[Fillable(['id', 'project_id', 'parent_id', 'key', 'sequence_number', 'title', 'content', 'status', 'created_by', 'updated_by'])]
class ProjectDocumentModel extends Model implements Archivable
{
    /** @use HasFactory<ProjectDocumentModelFactory> */
    use HasArchivableColumns, HasAuditableColumns, HasFactory, HasFilters, HasUlids;

    /** Maximum allowed nesting depth (0, 1, 2 — a document at MAX_DEPTH cannot have children). */
    public const int MAX_DEPTH = 2;

    protected $table = 'project_documents';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'status'      => ProjectDocumentStatus::class,
            'depth'       => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProjectDocumentModel $document): void {
            $document->applyHierarchy();
        });

        static::updating(function (ProjectDocumentModel $document): void {
            if ($document->isDirty('parent_id') || $document->isDirty('project_id')) {
                $document->applyHierarchy();
            }
        });
    }

    protected function applyHierarchy(): void
    {
        if ($this->parent_id === null) {
            $this->path = $this->id;
            $this->depth = 0;

            return;
        }

        if ($this->parent_id === $this->id) {
            throw new DomainException('A document cannot be its own parent.');
        }

        $parent = static::query()->select(['id', 'project_id', 'path', 'depth'])->findOrFail($this->parent_id);

        if ($parent->project_id !== $this->project_id) {
            throw new DomainException('A child document must belong to the same project as its parent.');
        }

        if ($this->exists && in_array($this->id, explode('.', (string) $parent->path), true)) {
            throw new DomainException('A document cannot be moved under its own descendant.');
        }

        if ($parent->depth >= self::MAX_DEPTH) {
            throw new DomainException('Maximum document nesting depth ('.(self::MAX_DEPTH + 1).' levels) exceeded.');
        }

        $this->path = $parent->path.'.'.$this->id;
        $this->depth = $parent->depth + 1;
    }

    /**
     * Allows route-model binding by ULID id or by the human-readable key (e.g. DOC-PROJ-1).
     *
     * @param  Builder<ProjectDocumentModel>  $query
     * @return Builder<ProjectDocumentModel>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where(function (Builder $q) use ($value): void {
            $q->where($this->getKeyName(), $value)->orWhere('key', $value);
        });
    }

    public function wasStatusChangedToArchived(): bool
    {
        return $this->isDirty('status') && $this->status === ProjectDocumentStatus::Archived;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(TaskModel::class, 'project_document_task', 'project_document_id', 'task_id')
            ->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(TagModel::class, 'taggable', relatedPivotKey: 'tag_id')->withPivot('created_at');
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

    public static function newFactory(): ProjectDocumentModelFactory
    {
        return ProjectDocumentModelFactory::new();
    }

    public static function allowedFilters(): array
    {
        return [
            new FilterDefinition(TagFilter::class, []),
            new FilterDefinition(TaskFilter::class, []),
        ];
    }
}
