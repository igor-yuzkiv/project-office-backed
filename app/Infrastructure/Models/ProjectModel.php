<?php

namespace App\Infrastructure\Models;

use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

#[Fillable(['id', 'name', 'created_by', 'updated_by'])]
class ProjectModel extends Model
{
    use HasAuditableColumns, HasUlids, Searchable;

    protected $table = 'projects';

    public $incrementing = false;

    protected function casts(): array
    {
        return [];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by');
    }
}
