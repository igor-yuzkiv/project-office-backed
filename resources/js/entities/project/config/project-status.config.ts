import { STATUS_COLORS } from '@/shared/components/status-pill'
import type { ProjectStatusMetadata, ProjectStatusMetadataMap } from '../types/project-status.types'

export const ProjectStatusMap: ProjectStatusMetadataMap = {
    draft: { label: 'Draft', value: 'draft', colors: STATUS_COLORS.open },
    active: { label: 'Active', value: 'active', colors: STATUS_COLORS.progress },
    on_hold: { label: 'On Hold', value: 'on_hold', colors: STATUS_COLORS.test },
    completed: { label: 'Completed', value: 'completed', colors: STATUS_COLORS.done },
    archived: { label: 'Archived', value: 'archived', colors: STATUS_COLORS.closed },
    declined: { label: 'Declined', value: 'declined', colors: STATUS_COLORS.declined },
}

export function projectStatusOptions(): ProjectStatusMetadata[] {
    return Object.values(ProjectStatusMap)
}
