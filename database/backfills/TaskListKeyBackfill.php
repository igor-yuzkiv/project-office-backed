<?php

namespace Database\Backfills;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Assigns `key` and `sequence_number` to task lists created before those columns existed.
 * Numbering restarts per project, in `(created_at, id)` order.
 */
class TaskListKeyBackfill
{
    public function run(): void
    {
        $prefixes = DB::table('projects')->pluck('prefix', 'id');

        $taskLists = DB::table('task_lists')
            ->orderBy('project_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'project_id']);

        $sequenceNumbers = [];

        foreach ($taskLists as $taskList) {
            $prefix = $prefixes[$taskList->project_id] ?? null;

            if ($prefix === null) {
                throw new RuntimeException("Task list {$taskList->id} references project {$taskList->project_id}, which has no prefix.");
            }

            $sequenceNumber = ($sequenceNumbers[$taskList->project_id] ?? 0) + 1;
            $sequenceNumbers[$taskList->project_id] = $sequenceNumber;

            DB::table('task_lists')->where('id', $taskList->id)->update([
                'key'             => $prefix.'-TL-'.$sequenceNumber,
                'sequence_number' => $sequenceNumber,
            ]);
        }
    }
}
