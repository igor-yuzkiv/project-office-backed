<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask;

/**
 * The first line a workflow comment starts with. The kind of a comment is stored on the row
 * (`CommentKind`); these prefixes shape the text a reader sees.
 */
final class CheckpointComment
{
    public const PREFIX = '# Checkpoint: ';

    public const HANDOFF_PREFIX = '# Handoff';

    public const START_PREFIX = '# Start';
}
