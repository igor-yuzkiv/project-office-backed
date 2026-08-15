<?php

namespace App\Domains\Comment\AuditRecords;

use App\Domains\ProjectDocument\Models\ProjectDocumentModel;
use App\Domains\Task\Models\TaskModel;
use App\Domains\TaskList\Models\TaskListModel;
use App\Infrastructure\Models\Contracts\Commentable;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One type for every carrier: the subject is whoever was commented on, and the frontend builds
 * the route from type plus subject type.
 */
class CommentCreatedAuditRecord implements AuditRecord
{
    use ResolvesActorName;

    private const EXCERPT_LIMIT = 500;

    public function __construct(
        private readonly Commentable&Model $commentable,
        private readonly string $content,
    ) {}

    public function type(): string
    {
        return 'comment.created';
    }

    public function title(): string
    {
        return "{$this->actorName()} commented on {$this->carrierName()}";
    }

    public function description(): ?string
    {
        return Str::limit($this->content, self::EXCERPT_LIMIT, '…');
    }

    public function subject(): ?Model
    {
        return $this->commentable;
    }

    /**
     * A carrier with no name of its own still reads as a sentence, so a new Commentable does not
     * have to touch this class before it can be commented on.
     */
    private function carrierName(): string
    {
        return match ($this->commentable::class) {
            TaskModel::class            => (string) $this->commentable->key,
            TaskListModel::class        => "«{$this->commentable->name}»",
            ProjectDocumentModel::class => "«{$this->commentable->title}»",
            default                     => 'a '.str_replace('_', ' ', Str::snake(Str::replaceLast('Model', '', class_basename($this->commentable)))),
        };
    }
}
