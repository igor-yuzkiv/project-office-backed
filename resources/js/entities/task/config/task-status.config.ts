import { STATUS_COLORS } from '@/shared/components/status-pill'
import type { TaskStatusMetadata, TaskStatusMetadataMap } from '../types'

export const TaskStatusMap: TaskStatusMetadataMap = {
    backlog: { label: 'Backlog', value: 'backlog', colors: STATUS_COLORS.backlog },
    open: { label: 'Open', value: 'open', colors: STATUS_COLORS.open },
    ready_for_development: {
        label: 'Ready for development',
        value: 'ready_for_development',
        colors: STATUS_COLORS.ready,
    },
    in_progress: { label: 'In progress', value: 'in_progress', colors: STATUS_COLORS.progress },
    ready_to_test: { label: 'Ready to test', value: 'ready_to_test', colors: STATUS_COLORS.test },
    completed: { label: 'Completed', value: 'completed', colors: STATUS_COLORS.done },
    closed: { label: 'Closed', value: 'closed', colors: STATUS_COLORS.closed },
    declined: { label: 'Declined', value: 'declined', colors: STATUS_COLORS.declined },
}

export function taskStatusOptions(): TaskStatusMetadata[] {
    return Object.values(TaskStatusMap)
}
