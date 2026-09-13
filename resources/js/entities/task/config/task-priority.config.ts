import { STATUS_COLORS } from '@/shared/components/status-pill'
import type { TaskPriorityMetadata, TaskPriorityMetadataMap } from '../types'

// Urgent is the one pair outside STATUS_COLORS: a step darker than High so the two stay apart.
const URGENT_COLORS = { light: { fg: '#991b1b', bg: '#fee2e2' }, dark: { fg: '#fca5a5', bg: '#4c1d1d' } } as const

export const TaskPriorityMap: TaskPriorityMetadataMap = {
    None: { label: 'None', value: 0, name: 'None', colors: STATUS_COLORS.open, icon: 'hugeicons:minus-sign' },
    Low: { label: 'Low', value: 10, name: 'Low', colors: STATUS_COLORS.progress, icon: 'hugeicons:arrow-down-01' },
    Medium: { label: 'Medium', value: 50, name: 'Medium', colors: STATUS_COLORS.test, icon: 'hugeicons:minus-sign' },
    High: { label: 'High', value: 75, name: 'High', colors: STATUS_COLORS.declined, icon: 'hugeicons:arrow-up-01' },
    Urgent: { label: 'Urgent', value: 100, name: 'Urgent', colors: URGENT_COLORS, icon: 'hugeicons:arrow-up-double' },
}

export function taskPriorityOptions(): TaskPriorityMetadata[] {
    return Object.values(TaskPriorityMap)
}
