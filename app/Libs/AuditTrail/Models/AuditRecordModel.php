<?php

namespace App\Libs\AuditTrail\Models;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\User\Models\UserModel;
use App\Libs\AuditTrail\SubjectType;
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\LookupFilter;
use App\Libs\EloquentFilters\Filters\TextFilter;
use Carbon\CarbonImmutable;
use Database\Factories\AuditRecordModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property string|null $project_id
 * @property CarbonImmutable|null $created_at
 * @property-read ProjectModel|null $project
 *
 * @method static Builder filter(array $filters)
 */
#[Fillable(['id', 'type', 'title', 'description', 'subject_type', 'subject_id', 'project_id', 'created_by', 'created_at'])]
class AuditRecordModel extends Model
{
    /** @use HasFactory<AuditRecordModelFactory> */
    use HasFactory, HasUlids;

    use HasFilters {
        scopeFilter as applyFilters;
    }

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

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    /**
     * Clients filter subject_type by the short key the resource returns; the column holds the
     * class, so the values are translated before the generic filters see them.
     *
     * @param  Builder<AuditRecordModel>  $query
     * @return Builder<AuditRecordModel>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $translated = array_map(function (array $payload): array {
            if (($payload['field_name'] ?? null) !== 'subject_type') {
                return $payload;
            }

            $value = $payload['value'] ?? null;
            $payload['value'] = is_array($value)
                ? array_map(fn ($key) => is_string($key) ? SubjectType::classOf($key) : $key, $value)
                : (is_string($value) ? SubjectType::classOf($value) : $value);

            return $payload;
        }, $filters);

        return $this->applyFilters($query, $translated);
    }

    public static function allowedFilters(): array
    {
        return [
            new FilterDefinition(TextFilter::class, ['type', 'subject_type']),
            new FilterDefinition(LookupFilter::class, ['project_id']),
        ];
    }

    public static function newFactory(): AuditRecordModelFactory
    {
        return AuditRecordModelFactory::new();
    }
}
