import type { TaskListStatusMetadata, TaskListStatusMetadataMap } from '../types'

export const TaskListStatusMap: TaskListStatusMetadataMap = {
    open: { label: 'Open', value: 'open', color: '#3b82f6' },
    in_progress: { label: 'In Progress', value: 'in_progress', color: '#f59e0b' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
}

export function taskListStatusOptions(): TaskListStatusMetadata[] {
    return Object.values(TaskListStatusMap)
}
