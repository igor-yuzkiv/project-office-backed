import type { TaskStatusCounts } from '@/entities/task/types'

export type TaskListProgress = { done: number; inProgress: number; total: number }

const DONE_STATUSES = ['ready_to_test', 'completed', 'closed'] as const

/** Declined tasks leave the list, so they count neither as done nor towards the total. */
export function taskListProgress(counts: Partial<TaskStatusCounts> | undefined): TaskListProgress {
    const count = (status: keyof TaskStatusCounts) => counts?.[status] ?? 0
    const all = Object.values(counts ?? {}).reduce((sum, value) => sum + value, 0)

    return {
        done: DONE_STATUSES.reduce((sum, status) => sum + count(status), 0),
        inProgress: count('in_progress'),
        total: all - count('declined'),
    }
}
