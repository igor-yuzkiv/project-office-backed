import type { ProjectDocumentStatusMetadataMap } from '../types/project-document-status.types'

export const ProjectDocumentStatusMap: ProjectDocumentStatusMetadataMap = {
    draft: { label: 'Draft', value: 'draft', color: '#6b7280' },
    in_review: { label: 'In Review', value: 'in_review', color: '#f59e0b' },
    active: { label: 'Active', value: 'active', color: '#22c55e' },
    deprecated: { label: 'Deprecated', value: 'deprecated', color: '#f97316' },
    archived: { label: 'Archived', value: 'archived', color: '#475569' },
}
