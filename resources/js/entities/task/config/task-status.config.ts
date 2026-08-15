import type { TaskStatusMetadata, TaskStatusMetadataMap, TaskStatusValue } from '../types'

export const TaskStatusMap: TaskStatusMetadataMap = {
    backlog: { label: 'Backlog', value: 'backlog', color: '#94a3b8' },
    open: { label: 'Open', value: 'open', color: '#3b82f6' },
    ready_for_development: { label: 'Ready for development', value: 'ready_for_development', color: '#8b5cf6' },
    in_progress: { label: 'In progress', value: 'in_progress', color: '#f59e0b' },
    ready_to_test: { label: 'Ready to test', value: 'ready_to_test', color: '#06b6d4' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
    closed: { label: 'Closed', value: 'closed', color: '#6b7280' },
}

export function taskStatusOptions(): TaskStatusMetadata[] {
    return Object.values(TaskStatusMap)
}

/** Statuses that mean the work is behind us — what "done" counts as when tasks are tallied. */
export const TASK_DONE_STATUSES: TaskStatusValue[] = ['completed', 'closed']

export function isTaskDone(status: TaskStatusValue): boolean {
    return TASK_DONE_STATUSES.includes(status)
}
