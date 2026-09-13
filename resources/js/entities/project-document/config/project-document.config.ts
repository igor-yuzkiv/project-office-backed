import { STATUS_COLORS } from '@/shared/components/status-pill'
import type {
    ProjectDocumentStatusMetadata,
    ProjectDocumentStatusMetadataMap,
} from '../types/project-document-status.types'

export const ProjectDocumentStatusMap: ProjectDocumentStatusMetadataMap = {
    draft: { label: 'Draft', value: 'draft', colors: STATUS_COLORS.open },
    in_review: { label: 'In Review', value: 'in_review', colors: STATUS_COLORS.test },
    active: { label: 'Active', value: 'active', colors: STATUS_COLORS.done },
    deprecated: { label: 'Deprecated', value: 'deprecated', colors: STATUS_COLORS.declined },
    archived: { label: 'Archived', value: 'archived', colors: STATUS_COLORS.closed },
}

export function projectDocumentStatusOptions(): ProjectDocumentStatusMetadata[] {
    return Object.values(ProjectDocumentStatusMap)
}
