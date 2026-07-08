export type TaskStatusValue =
    | 'backlog'
    | 'open'
    | 'ready_for_development'
    | 'in_progress'
    | 'ready_to_test'
    | 'completed'
    | 'closed'

import type { HexColor } from '@/shared/types'

export type TaskStatusMetadata = {
    label: string
    value: TaskStatusValue
    color: HexColor
}

export type TaskStatusMetadataMap = Record<TaskStatusValue, TaskStatusMetadata>
