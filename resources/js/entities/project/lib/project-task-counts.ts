import type { TaskStatusCounts } from '@/entities/task/types'

export type ProjectTaskCounts = {
    open: number
    inProgress: number
    toTest: number
    backlog: number
    closed: number
    tasks: number
}

/** The task view each derived count opens, so a number on a card is also the way into its tasks. */
export const PROJECT_COUNT_VIEWS = {
    open: 'all_open',
    backlog: 'all_backlogged',
    closed: 'all_closed',
    tasks: 'all',
} as const satisfies Partial<Record<keyof ProjectTaskCounts, string>>

/**
 * The same grouping as the backend task views: open is everything being worked or waiting to be,
 * closed folds declined in, and tasks is everything — the same figure as the Tasks tab and the
 * `all` view it opens.
 */
export function projectTaskCounts(counts: Partial<TaskStatusCounts> | undefined): ProjectTaskCounts {
    const count = (status: keyof TaskStatusCounts) => counts?.[status] ?? 0
    return {
        open: count('open') + count('ready_for_development') + count('in_progress'),
        inProgress: count('in_progress'),
        toTest: count('ready_to_test'),
        backlog: count('backlog'),
        closed: count('closed') + count('declined'),
        tasks: Object.values(counts ?? {}).reduce((sum, value) => sum + value, 0),
    }
}
