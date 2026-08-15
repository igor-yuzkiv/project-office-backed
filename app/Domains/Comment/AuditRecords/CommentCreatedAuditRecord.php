<?php

namespace App\Domains\Comment\AuditRecords;

use App\Infrastructure\Models\Contracts\Commentable;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
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
    use DescribesSubject, ResolvesActorName;

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
        return "{$this->actorName()} commented on {$this->subjectName($this->commentable)}";
    }

    public function description(): ?string
    {
        return Str::limit($this->content, self::EXCERPT_LIMIT, '…');
    }

    public function subject(): ?Model
    {
        return $this->commentable;
    }
}
