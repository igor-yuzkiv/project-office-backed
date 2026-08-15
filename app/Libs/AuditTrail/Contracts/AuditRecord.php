<?php

namespace App\Libs\AuditTrail\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuditRecord
{
    public function type(): string;

    public function title(): string;

    public function description(): ?string;

    public function subject(): ?Model;
}
