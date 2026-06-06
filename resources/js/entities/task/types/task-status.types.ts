export type TaskStatusValue = 'open' | 'in_progress' | 'completed' | 'closed'

import type { HexColor } from '@/shared/types'

export type TaskStatusMetadata = {
    label: string
    value: TaskStatusValue
    color: HexColor
}

export type TaskStatusMetadataMap = Record<TaskStatusValue, TaskStatusMetadata>
