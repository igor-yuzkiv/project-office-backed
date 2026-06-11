import type { ProjectStatusMetadata, ProjectStatusMetadataMap } from '../types/project-status.types'

export const ProjectStatusMap: ProjectStatusMetadataMap = {
    draft: { label: 'Draft', value: 'draft', color: '#6b7280' },
    active: { label: 'Active', value: 'active', color: '#3b82f6' },
    inactive: { label: 'Inactive', value: 'inactive', color: '#9ca3af' },
    archived: { label: 'Archived', value: 'archived', color: '#475569' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
    on_hold: { label: 'On Hold', value: 'on_hold', color: '#f59e0b' },
    cancelled: { label: 'Cancelled', value: 'cancelled', color: '#ef4444' },
}

export function projectStatusOptions(): ProjectStatusMetadata[] {
    return Object.values(ProjectStatusMap)
}
