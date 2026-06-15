<?php

namespace App\Infrastructure\Models\Contracts;

interface Archivable
{
    public function wasStatusChangedToArchived(): bool;
}
