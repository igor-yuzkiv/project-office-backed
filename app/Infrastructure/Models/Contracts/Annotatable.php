<?php

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Annotatable
{
    public function annotations(): MorphMany;
}
