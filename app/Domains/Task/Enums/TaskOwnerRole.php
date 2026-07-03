<?php

namespace App\Domains\Task\Enums;

enum TaskOwnerRole: string
{
    case ProjectManager = 'Project Manager';
    case Executor = 'Executor';
    case QA = 'QA';
    case Supervisor = 'Supervisor';
}
