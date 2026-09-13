import type { ThemedStatusColors } from '@/shared/types'

export type TaskPriorityName = 'None' | 'Low' | 'Medium' | 'High' | 'Urgent'

export type TaskPriorityDto = {
    value: number
    name: TaskPriorityName
}

export type TaskPriorityMetadata = TaskPriorityDto & {
    label: string
    colors: ThemedStatusColors
    icon: string
}

export type TaskPriorityMetadataMap = Record<TaskPriorityName, TaskPriorityMetadata>
