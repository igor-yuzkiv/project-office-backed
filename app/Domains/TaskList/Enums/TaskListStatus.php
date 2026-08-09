<?php

namespace App\Domains\TaskList\Enums;

enum TaskListStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
