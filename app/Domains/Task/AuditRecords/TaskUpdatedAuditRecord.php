<?php

namespace App\Domains\Task\AuditRecords;

use App\Domains\Task\Models\TaskModel;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class TaskUpdatedAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    /**
     * Bounded to the columns UpdateTaskHandler writes. `status` is absent on purpose: it has its
     * own event; `updated_at` and `updated_by` change on every write (HasAuditableColumns).
     *
     * Adding a column to UpdateTaskHandler means adding it here too: a column missing from this
     * map is not merely unnamed, it drops out of the change set and can silence the event.
     */
    private const FIELD_NAMES = [
        'task_list_id' => 'task list',
        'name'         => 'name',
        'description'  => 'description',
        'start_date'   => 'start date',
        'due_date'     => 'due date',
        'priority'     => 'priority',
    ];

    /**
     * @param  string[]  $changedColumns
     */
    public function __construct(
        private readonly TaskModel $task,
        private readonly array $changedColumns,
    ) {}

    /**
     * @param  string[]  $changedColumns  keys of TaskModel::getChanges() taken right after update()
     * @return string[] the columns this event actually reports on
     */
    public static function reportableColumns(array $changedColumns): array
    {
        return array_values(array_intersect($changedColumns, array_keys(self::FIELD_NAMES)));
    }

    public function type(): string
    {
        return 'task.updated';
    }

    public function title(): string
    {
        return "{$this->actorName()} updated {$this->task->key}";
    }

    public function description(): ?string
    {
        $names = array_map(fn (string $column) => self::FIELD_NAMES[$column], $this->changedColumns);

        if ($names === []) {
            return null;
        }

        $last = array_pop($names);
        $list = $names === [] ? $last : implode(', ', $names).' and '.$last;

        return "Changed {$list}";
    }

    public function subject(): ?Model
    {
        return $this->task;
    }
}
