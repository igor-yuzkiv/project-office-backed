import type { MaybeRefOrGetter } from 'vue'
import type { TaskPriorityMetadata, TaskPriorityMetadataMap } from '../types/task-priority.types'
import type { TaskStatusMetadata, TaskStatusMetadataMap } from '../types/task-status.types'
import type { TaskSearchParams } from '../types/task.types'

export const TaskQueryKey = {
    all: ['tasks'] as const,
    search: (params: MaybeRefOrGetter<TaskSearchParams>) => [...TaskQueryKey.all, 'search', params] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...TaskQueryKey.all, 'detail', id] as const,
}

export const TaskStatusMap: TaskStatusMetadataMap = {
    open: { label: 'Open', value: 'open', color: '#3b82f6' },
    in_progress: { label: 'In Progress', value: 'in_progress', color: '#f59e0b' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
    closed: { label: 'Closed', value: 'closed', color: '#6b7280' },
}

export const TaskPriorityMap: TaskPriorityMetadataMap = {
    Low: { label: 'Low', value: 10, name: 'Low', color: '#3b82f6' },
    Medium: { label: 'Medium', value: 50, name: 'Medium', color: '#f59e0b' },
    High: { label: 'High', value: 100, name: 'High', color: '#ef4444' },
}

export function taskStatusOptions(): TaskStatusMetadata[] {
    return Object.values(TaskStatusMap)
}

export function taskPriorityOptions(): TaskPriorityMetadata[] {
    return Object.values(TaskPriorityMap)
}
