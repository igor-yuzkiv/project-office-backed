import type { TaskPriorityMetadata, TaskPriorityMetadataMap } from '../types'

export const TaskPriorityMap: TaskPriorityMetadataMap = {
    Low: { label: 'Low', value: 10, name: 'Low', color: '#3b82f6', icon: 'hugeicons:arrow-down-01' },
    Medium: { label: 'Medium', value: 50, name: 'Medium', color: '#f59e0b', icon: 'hugeicons:minus-sign' },
    High: { label: 'High', value: 100, name: 'High', color: '#ef4444', icon: 'hugeicons:arrow-up-01' },
}

export function taskPriorityOptions(): TaskPriorityMetadata[] {
    return Object.values(TaskPriorityMap)
}
