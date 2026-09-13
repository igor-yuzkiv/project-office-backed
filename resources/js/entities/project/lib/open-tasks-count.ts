import type { TaskStatusValue } from '@/entities/task/types'
import type { TaskStatusCounts } from '../types'

const CLOSED_TASK_STATUSES: readonly TaskStatusValue[] = ['completed', 'closed', 'declined']

export function openTasksCount(counts: Partial<TaskStatusCounts> | undefined): number {
    if (!counts) return 0

    return (Object.entries(counts) as [TaskStatusValue, number][])
        .filter(([status]) => !CLOSED_TASK_STATUSES.includes(status))
        .reduce((sum, [, count]) => sum + count, 0)
}
