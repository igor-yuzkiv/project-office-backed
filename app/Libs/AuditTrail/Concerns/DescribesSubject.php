<?php

namespace App\Libs\AuditTrail\Concerns;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * How an entity is named inside a feed sentence. It is the one place the lib knows domain model
 * classes: a display name has to come from somewhere, and every alternative either duplicates
 * this map per domain or pushes audit concerns into the models themselves.
 */
trait DescribesSubject
{
    protected function subjectName(Model $subject): string
    {
        return match ($subject::class) {
            TaskModel::class            => (string) $subject->key,
            TaskListModel::class        => "«{$subject->name}»",
            ProjectModel::class         => "«{$subject->name}»",
            ProjectDocumentModel::class => "«{$subject->title}»",
            // A model this map has never heard of still reads as a sentence, so adding one
            // never breaks a record.
            default => 'a '.str_replace('_', ' ', Str::snake(Str::replaceLast('Model', '', class_basename($subject)))),
        };
    }
}
