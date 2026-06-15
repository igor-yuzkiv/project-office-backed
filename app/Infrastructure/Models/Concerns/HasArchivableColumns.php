<?php

namespace App\Infrastructure\Models\Concerns;

trait HasArchivableColumns
{
    protected function getArchivedAtColumn(): string
    {
        return 'archived_at';
    }

    protected function getArchivedByColumn(): string
    {
        return 'archived_by';
    }

    protected static function bootHasArchivableColumns(): void
    {
        static::saving(function ($model): void {
            if ($model->wasStatusChangedToArchived()) {
                $model->{$model->getArchivedAtColumn()} = now();
                $model->{$model->getArchivedByColumn()} = auth()->id();
            } elseif ($model->isDirty('status') && $model->getRawOriginal('status') === 'archived') {
                $model->{$model->getArchivedAtColumn()} = null;
                $model->{$model->getArchivedByColumn()} = null;
            }
        });
    }
}
