<?php

namespace App\Infrastructure\Models\Concerns;

trait HasAuditableColumns
{
    protected function getCreatedByColumn(): string
    {
        return 'created_by';
    }

    protected function getUpdatedByColumn(): string
    {
        return 'updated_by';
    }

    protected static function bootHasAuditableColumns(): void
    {
        static::creating(function ($model) {
            $model->{$model->getCreatedByColumn()} ??= auth()->id();
            $model->{$model->getUpdatedByColumn()} ??= auth()->id();
        });

        static::updating(function ($model) {
            $model->{$model->getUpdatedByColumn()} = auth()->id();
        });
    }
}
