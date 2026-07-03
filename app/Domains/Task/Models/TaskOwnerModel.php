<?php

namespace App\Domains\Task\Models;

use App\Domains\Task\Enums\TaskOwnerRole;
use App\Domains\User\Models\UserModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $task_id
 * @property string $user_id
 * @property TaskOwnerRole|null $role
 * @property bool $is_primary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read TaskModel $task
 * @property-read UserModel $user
 */
#[Fillable(['task_id', 'user_id', 'role', 'is_primary'])]
class TaskOwnerModel extends Model
{
    use HasUlids;

    protected $table = 'task_owners';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'role'       => TaskOwnerRole::class,
            'is_primary' => 'boolean',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
