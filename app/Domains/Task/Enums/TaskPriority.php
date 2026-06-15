<?php

namespace App\Domains\Task\Enums;

enum TaskPriority: int
{
    case None = 0;
    case Low = 10;
    case Medium = 50;
    case High = 75;
    case Urgent = 100;
}
