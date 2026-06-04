import type { MaybeRefOrGetter } from 'vue'
import type {
    TaskPriorityMetadata,
    TaskPriorityMetadataMap,
    TaskSearchParams,
    TaskStatusMetadata,
    TaskStatusMetadataMap,
} from '../types'

export const TaskQueryKey = {
    all: ['tasks'] as const,
    search: (params: MaybeRefOrGetter<TaskSearchParams>) => [...TaskQueryKey.all, 'search', params] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...TaskQueryKey.all, 'detail', id] as const,
}

export const TaskStatusMap: TaskStatusMetadataMap = {
    open: { label: 'Open', value: 'open' },
    in_progress: { label: 'In Progress', value: 'in_progress' },
    completed: { label: 'Completed', value: 'completed' },
    closed: { label: 'Closed', value: 'closed' },
}

export const TaskPriorityMap: TaskPriorityMetadataMap = {
    Low: { label: 'Low', value: 10, name: 'Low' },
    Medium: { label: 'Medium', value: 50, name: 'Medium' },
    High: { label: 'High', value: 100, name: 'High' },
}

export function taskStatusOptions(): TaskStatusMetadata[] {
    return Object.values(TaskStatusMap)
}

export function taskPriorityOptions(): TaskPriorityMetadata[] {
    return Object.values(TaskPriorityMap)
}
