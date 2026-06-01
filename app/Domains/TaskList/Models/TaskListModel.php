<?php

namespace App\Domains\TaskList\Models;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\TextFilter;
use Database\Factories\TaskListModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/** @method static \Illuminate\Database\Eloquent\Builder filter(array $filters) */
#[Fillable(['id', 'project_id', 'name', 'created_by', 'updated_by'])]
class TaskListModel extends Model
{
    /** @use HasFactory<TaskListModelFactory> */
    use HasAuditableColumns, HasFactory, HasFilters, HasUlids, Searchable;

    protected $table = 'task_lists';

    public $incrementing = false;

    public function toSearchableArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'project_id' => $this->project_id,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
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
            new FilterDefinition(TextFilter::class, ['name', 'project_id']),
        ];
    }
}
