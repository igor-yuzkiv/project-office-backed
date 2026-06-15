import type { ProjectStatusMetadata, ProjectStatusMetadataMap } from '../types/project-status.types'

export const ProjectStatusMap: ProjectStatusMetadataMap = {
    draft: { label: 'Draft', value: 'draft', color: '#6b7280' },
    active: { label: 'Active', value: 'active', color: '#3b82f6' },
    on_hold: { label: 'On Hold', value: 'on_hold', color: '#f59e0b' },
    completed: { label: 'Completed', value: 'completed', color: '#22c55e' },
    archived: { label: 'Archived', value: 'archived', color: '#475569' },
}

export function projectStatusOptions(): ProjectStatusMetadata[] {
    return Object.values(ProjectStatusMap)
}
