<?php

namespace App\Domains\Task\Enums;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Open = 'open';
    case ReadyForDevelopment = 'ready_for_development';
    case InProgress = 'in_progress';
    case ReadyToTest = 'ready_to_test';
    case Completed = 'completed';
    case Closed = 'closed';
}
