<?php

namespace App\Domains\Task\Enums;

enum TaskPriority: int
{
    case Low = 10;
    case Medium = 50;
    case High = 100;
}
