import type { TaskStatusMetadata, TaskStatusMetadataMap } from '../types'

export const TaskStatusMap: TaskStatusMetadataMap = {
    open: { label: 'Open', value: 'open', color: '#3b82f6' },
    in_progress: { label: 'In Progress', value: 'in_progress', color: '#f59e0b' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
    closed: { label: 'Closed', value: 'closed', color: '#6b7280' },
}

export function taskStatusOptions(): TaskStatusMetadata[] {
    return Object.values(TaskStatusMap)
}
