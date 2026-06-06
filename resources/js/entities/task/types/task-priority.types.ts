import type { HexColor } from '@/shared/types'

export type TaskPriorityName = 'Low' | 'Medium' | 'High'

export type TaskPriorityDto = {
    value: number
    name: TaskPriorityName
}

export type TaskPriorityMetadata = TaskPriorityDto & {
    label: string
    color: HexColor
}

export type TaskPriorityMetadataMap = Record<TaskPriorityName, TaskPriorityMetadata>
