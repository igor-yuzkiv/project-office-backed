<?php

namespace App\Libs\AuditTrail;

use App\Domains\Project\Models\ProjectModel;
use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * The short subject key the API speaks (`task`, `project_document`) against the model class the
 * table stores. Both the resource and the subject_type filter go through here, so a key means the
 * same thing on the way out and on the way in.
 */
final class SubjectType
{
    /** @var array<string, class-string<Model>> */
    private const array MAP = [
        'task'             => TaskModel::class,
        'task_list'        => TaskListModel::class,
        'project'          => ProjectModel::class,
        'project_document' => ProjectDocumentModel::class,
    ];

    public static function keyOf(string $class): string
    {
        $key = array_search($class, self::MAP, true);

        // A class outside the map still gets a readable key rather than a namespace.
        return $key === false ? Str::snake(Str::replaceLast('Model', '', class_basename($class))) : $key;
    }

    /**
     * The stored class for a key; a value that is already a class, or an unknown key, passes
     * through untouched so the filter simply matches nothing.
     */
    public static function classOf(string $keyOrClass): string
    {
        return self::MAP[$keyOrClass] ?? $keyOrClass;
    }

    /**
     * The id of the project a subject belongs to — the project itself, or whatever carries a
     * project_id.
     */
    public static function projectIdOf(Model $subject): ?string
    {
        $projectId = $subject instanceof ProjectModel ? $subject->getKey() : $subject->getAttribute('project_id');

        return $projectId === null || $projectId === '' ? null : (string) $projectId;
    }

    /** The human-readable key shown next to a subject; a project is addressed by its prefix. */
    public static function keyAttributeOf(Model $subject): ?string
    {
        $key = $subject instanceof ProjectModel ? $subject->getAttribute('prefix') : $subject->getAttribute('key');

        return $key === null ? null : (string) $key;
    }

    public static function nameOf(Model $subject): ?string
    {
        $name = $subject instanceof ProjectDocumentModel ? $subject->getAttribute('title') : $subject->getAttribute('name');

        return $name === null ? null : (string) $name;
    }
}
