import { STATUS_COLORS } from '@/shared/components/status-pill'
import type { TaskStatusMetadata, TaskStatusMetadataMap, TaskStatusValue } from '../types'

export const TaskStatusMap: TaskStatusMetadataMap = {
    backlog: { label: 'Backlog', value: 'backlog', colors: STATUS_COLORS.backlog },
    open: { label: 'Open', value: 'open', colors: STATUS_COLORS.open },
    ready_for_development: {
        label: 'Ready for development',
        value: 'ready_for_development',
        colors: STATUS_COLORS.ready,
    },
    in_progress: { label: 'In progress', value: 'in_progress', colors: STATUS_COLORS.progress },
    ready_to_test: { label: 'Ready to test', value: 'ready_to_test', colors: STATUS_COLORS.test },
    completed: { label: 'Completed', value: 'completed', colors: STATUS_COLORS.done },
    closed: { label: 'Closed', value: 'closed', colors: STATUS_COLORS.closed },
    declined: { label: 'Declined', value: 'declined', colors: STATUS_COLORS.declined },
}

export function taskStatusOptions(): TaskStatusMetadata[] {
    return Object.values(TaskStatusMap)
}

/** Statuses that mean the work is behind us — what "done" counts as when tasks are tallied. */
export const TASK_DONE_STATUSES: TaskStatusValue[] = ['completed', 'closed', 'declined']

export function isTaskDone(status: TaskStatusValue): boolean {
    return TASK_DONE_STATUSES.includes(status)
}
