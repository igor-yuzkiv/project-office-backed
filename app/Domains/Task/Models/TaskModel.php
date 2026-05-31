<?php

namespace App\Domains\Task\Models;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\TaskList\Models\TaskListModel;
use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use Database\Factories\TaskModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'project_id', 'task_list_id', 'key', 'sequence_number', 'name', 'description', 'priority', 'status', 'created_by', 'updated_by'])]
class TaskModel extends Model
{
    /** @use HasFactory<TaskModelFactory> */
    use HasAuditableColumns, HasFactory, HasUlids;

    protected $table = 'tasks';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status'   => TaskStatus::class,
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

    public static function newFactory(): TaskModelFactory
    {
        return TaskModelFactory::new();
    }
}
