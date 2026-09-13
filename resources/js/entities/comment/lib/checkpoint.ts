import type { IComment } from '../types'

// Mirrors CheckpointComment::PREFIX on the backend: the subject is the rest of the first line.
const PREFIX = '# Checkpoint: '

export function checkpointSubject(comment: Pick<IComment, 'kind' | 'content'>): string | null {
    if (comment.kind !== 'checkpoint') return null
    const firstLine = comment.content.split('\n', 1)[0] ?? ''
    return firstLine.startsWith(PREFIX) ? firstLine.slice(PREFIX.length).trim() : null
}

export function checkpointBody(comment: Pick<IComment, 'kind' | 'content'>): string {
    if (checkpointSubject(comment) === null) return comment.content
    return comment.content
        .split('\n')
        .slice(1)
        .join('\n')
        .replace(/^\s*\n/, '')
}
