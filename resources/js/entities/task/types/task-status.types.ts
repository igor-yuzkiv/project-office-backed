export type TaskStatusValue =
    | 'backlog'
    | 'open'
    | 'ready_for_development'
    | 'in_progress'
    | 'ready_to_test'
    | 'completed'
    | 'closed'
    | 'declined'

import type { ThemedStatusColors } from '@/shared/types'

export type TaskStatusMetadata = {
    label: string
    value: TaskStatusValue
    colors: ThemedStatusColors
}

export type TaskStatusMetadataMap = Record<TaskStatusValue, TaskStatusMetadata>

export type TaskStatusCounts = Record<TaskStatusValue, number>
