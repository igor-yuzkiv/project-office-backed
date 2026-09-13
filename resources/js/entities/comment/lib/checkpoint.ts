import type { IComment } from '../types'

// Mirrors CheckpointComment on the backend: a checkpoint's subject is the rest of its first line,
// a handoff's first line is only the `# Handoff` marker.
const CHECKPOINT_PREFIX = '# Checkpoint: '
const HANDOFF_PREFIX = '# Handoff'
const START_PREFIX = '# Start'

type CommentLike = Pick<IComment, 'kind' | 'content'>

export function checkpointSubject(comment: CommentLike): string | null {
    if (comment.kind !== 'checkpoint') return null
    const firstLine = firstLineOf(comment)
    return firstLine.startsWith(CHECKPOINT_PREFIX) ? firstLine.slice(CHECKPOINT_PREFIX.length).trim() : null
}

export function checkpointBody(comment: CommentLike): string {
    if (!hasHeadingLine(comment)) return comment.content
    return comment.content
        .split('\n')
        .slice(1)
        .join('\n')
        .replace(/^\s*\n/, '')
}

function hasHeadingLine(comment: CommentLike): boolean {
    if (comment.kind === 'checkpoint') return checkpointSubject(comment) !== null
    if (comment.kind === 'handoff') return firstLineOf(comment).startsWith(HANDOFF_PREFIX)
    if (comment.kind === 'start') return firstLineOf(comment).startsWith(START_PREFIX)
    return false
}

function firstLineOf(comment: CommentLike): string {
    return comment.content.split('\n', 1)[0] ?? ''
}
