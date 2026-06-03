<?php

namespace App\Domains\Task\ValueObjects;

use App\Domains\Task\Enums\TaskPriority;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
readonly class TaskPriorityData implements Arrayable
{
    public function __construct(
        public int $value,
        public string $name,
    ) {}

    public static function from(TaskPriority $priority): self
    {
        return new self(
            value: $priority->value,
            name: $priority->name,
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name'  => $this->name,
        ];
    }
}
