<?php

namespace App\Libs\AuditTrail\Models;

use App\Domains\User\Models\UserModel;
use Carbon\CarbonImmutable;
use Database\Factories\AuditRecordModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property CarbonImmutable|null $created_at
 */
#[Fillable(['id', 'type', 'title', 'description', 'subject_type', 'subject_id', 'created_by', 'created_at'])]
class AuditRecordModel extends Model
{
    /** @use HasFactory<AuditRecordModelFactory> */
    use HasFactory, HasUlids;

    /**
     * Timestamps stay on so created_at is cast to Carbon; only updated_at is dropped, as the
     * table has no such column.
     */
    public const UPDATED_AT = null;

    protected $table = 'audit_records';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function newFactory(): AuditRecordModelFactory
    {
        return AuditRecordModelFactory::new();
    }
}
