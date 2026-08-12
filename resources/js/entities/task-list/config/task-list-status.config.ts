import type { TaskListStatusMetadata, TaskListStatusMetadataMap } from '../types'

// Labels and colors mirror TaskStatusMap so a list and its tasks read the same way.
export const TaskListStatusMap: TaskListStatusMetadataMap = {
    backlog: { label: 'Backlog', value: 'backlog', color: '#94a3b8' },
    open: { label: 'Open', value: 'open', color: '#3b82f6' },
    ready_for_development: { label: 'Ready For Development', value: 'ready_for_development', color: '#8b5cf6' },
    in_progress: { label: 'In Progress', value: 'in_progress', color: '#f59e0b' },
    ready_to_test: { label: 'Ready To Test', value: 'ready_to_test', color: '#06b6d4' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
    closed: { label: 'Closed', value: 'closed', color: '#6b7280' },
}

export function taskListStatusOptions(): TaskListStatusMetadata[] {
    return Object.values(TaskListStatusMap)
}
