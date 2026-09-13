<?php

namespace App\Domains\Task\Actions\CliAgentWorkflow\CheckpointTask;

/**
 * A checkpoint is stored as an ordinary comment; this prefix is the only thing that tells it apart.
 */
final class CheckpointComment
{
    public const PREFIX = '# Checkpoint: ';

    public static function isCheckpoint(string $content): bool
    {
        return str_starts_with($content, self::PREFIX);
    }
}
