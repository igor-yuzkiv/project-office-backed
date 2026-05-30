<?php

namespace App\Domains\Attachment\Models;

use App\Domains\User\Models\UserModel;
use App\Infrastructure\Models\Concerns\HasAuditableColumns;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id',
    'original_name',
    'extension',
    'mime_type',
    'size_bytes',
    'storage_provider',
    'storage_key',
    'entity_type',
    'entity_id',
    'role',
    'created_by',
    'updated_by',
])]
class AttachmentModel extends Model
{
    use HasAuditableColumns, HasUlids;

    protected $table = 'attachments';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
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
