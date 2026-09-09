<?php

namespace App\Domains\TaskList\Enums;

/**
 * Mirrors App\Domains\Task\Enums\TaskStatus: a list moves through the same states as the
 * tasks in it. Kept as its own type so the TaskList domain does not depend on the Task
 * domain — when TaskStatus gains or loses a case, this enum follows it.
 */
enum TaskListStatus: string
{
    case Backlog = 'backlog';
    case Open = 'open';
    case ReadyForDevelopment = 'ready_for_development';
    case InProgress = 'in_progress';
    case ReadyToTest = 'ready_to_test';
    case Completed = 'completed';
    case Closed = 'closed';
    case Declined = 'declined';
}
