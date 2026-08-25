<?php

namespace App\Domains\ProjectDocument\Models;

use App\Domains\Annotation\Models\AnnotationModel;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\Comment\Models\CommentModel;
use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Enums\ProjectDocumentStatus;
use App\Domains\ProjectDocument\Exceptions\ProjectDocumentCyclicParentException;
use App\Domains\ProjectDocument\Exceptions\ProjectDocumentMaxDepthExceededException;
use App\Domains\ProjectDocument\Exceptions\ProjectDocumentParentProjectMismatchException;
use App\Domains\Tag\Models\TagModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasArchivableColumns;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use App\Infrastructure\Models\Contracts\Annotatable;
use App\Infrastructure\Models\Contracts\Archivable;
use App\Infrastructure\Models\Contracts\Commentable;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\LookupFilter;
use App\Libs\EloquentFilters\Filters\TagFilter;
use App\Libs\EloquentFilters\Filters\TaskFilter;
use Database\Factories\ProjectDocumentModelFactory;
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
 * @property string $id
 * @property string $project_id
 * @property string|null $parent_id
 * @property string $key
 * @property int $sequence_number
 * @property string $title
 * @property string|null $primary_version_id
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
 * @property-read Collection<int, CommentModel> $comments
 * @property-read Collection<int, AnnotationModel> $annotations
 * @property-read Collection<int, AttachmentModel> $attachments
 * @property-read Collection<int, ProjectDocumentVersionModel> $versions
 * @property-read ProjectDocumentVersionModel|null $primaryVersion
 * @property-read UserModel|null $archivedBy
 */
#[Fillable(['id', 'project_id', 'parent_id', 'key', 'sequence_number', 'title', 'primary_version_id', 'status', 'created_by', 'updated_by'])]
class ProjectDocumentModel extends Model implements Annotatable, Archivable, Commentable
{
    /** @use HasFactory<ProjectDocumentModelFactory> */
    use HasArchivableColumns, HasAuditableColumns, HasFactory, HasFilters, HasUlids, Searchable;

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

    /** The deepest a document may sit; zero-based, so the value is one less than the number of levels. */
    public static function maxDepth(): int
    {
        return (int) config('domains.project-document.max_depth');
    }

    /** The same limit counted the way a person says it aloud: a root plus everything under it. */
    public static function maxLevels(): int
    {
        return self::maxDepth() + 1;
    }

    /**
     * How the limit is put to the reader. Creating too deep is refused by the domain and moving
     * too deep by a validator, and the reader must not be able to tell which path they took.
     */
    public static function maxDepthMessage(): string
    {
        return 'Maximum document nesting depth ('.self::maxLevels().' levels) exceeded.';
    }

    public function canHaveChildren(): bool
    {
        return $this->depth < self::maxDepth();
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
            throw ProjectDocumentCyclicParentException::itself();
        }

        $parent = static::query()->select(['id', 'project_id', 'path', 'depth'])->findOrFail($this->parent_id);

        if ($parent->project_id !== $this->project_id) {
            throw ProjectDocumentParentProjectMismatchException::make();
        }

        if ($this->exists && in_array($this->id, explode('.', (string) $parent->path), true)) {
            throw ProjectDocumentCyclicParentException::ownDescendant();
        }

        if ($parent->depth >= self::maxDepth()) {
            throw ProjectDocumentMaxDepthExceededException::exceeded(self::maxDepthMessage());
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

    public function toSearchableArray(): array
    {
        return [
            'id'    => $this->id,
            'key'   => $this->key,
            'title' => $this->title,
        ];
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

    public function comments(): MorphMany
    {
        return $this->morphMany(CommentModel::class, 'commentable');
    }

    /** @return HasMany<ProjectDocumentVersionModel, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(ProjectDocumentVersionModel::class, 'project_document_id')
            ->orderBy('version_number');
    }

    /** @return BelongsTo<ProjectDocumentVersionModel, $this> */
    public function primaryVersion(): BelongsTo
    {
        return $this->belongsTo(ProjectDocumentVersionModel::class, 'primary_version_id');
    }

    /** A document may legitimately have no version at all, so null is an ordinary answer here. */
    public function effectiveVersion(): ?ProjectDocumentVersionModel
    {
        if ($this->primary_version_id !== null) {
            return $this->primaryVersion;
        }

        return $this->versions()->reorder()->orderByDesc('version_number')->first();
    }

    public function annotations(): MorphMany
    {
        return $this->morphMany(AnnotationModel::class, 'annotatable');
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
            new FilterDefinition(LookupFilter::class, ['project_id']),
        ];
    }
}
