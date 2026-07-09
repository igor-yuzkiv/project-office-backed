import type {
    ProjectDocumentStatusMetadata,
    ProjectDocumentStatusMetadataMap,
} from '../types/project-document-status.types'

export const ProjectDocumentStatusMap: ProjectDocumentStatusMetadataMap = {
    draft: { label: 'Draft', value: 'draft', color: '#6b7280' },
    in_review: { label: 'In Review', value: 'in_review', color: '#f59e0b' },
    active: { label: 'Active', value: 'active', color: '#22c55e' },
    deprecated: { label: 'Deprecated', value: 'deprecated', color: '#f97316' },
    archived: { label: 'Archived', value: 'archived', color: '#475569' },
}

export function projectDocumentStatusOptions(): ProjectDocumentStatusMetadata[] {
    return Object.values(ProjectDocumentStatusMap)
}

// Mirrors ProjectDocumentModel::MAX_DEPTH on the backend — a document at this depth
// cannot have children (depth 0, 1, 2; the root is depth 0).
export const PROJECT_DOCUMENT_MAX_DEPTH = 2

export function canProjectDocumentHaveChildren(depth: number): boolean {
    return depth < PROJECT_DOCUMENT_MAX_DEPTH
}
