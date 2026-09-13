<?php

namespace App\Domains\Comment\Enums;

/**
 * What a comment is in the task workflow. Set by the action that writes it; a plain comment
 * from a person is `Comment`.
 */
enum CommentKind: string
{
    case Comment = 'comment';
    case Start = 'start';
    case Checkpoint = 'checkpoint';
    case Handoff = 'handoff';
}
