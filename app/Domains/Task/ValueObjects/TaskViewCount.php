<?php

namespace App\Domains\Task\ValueObjects;

readonly class TaskViewCount
{
    public function __construct(
        public TaskView $view,
        public int $count,
    ) {}
}
