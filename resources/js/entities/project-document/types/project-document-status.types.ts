import type { ThemedStatusColors } from '@/shared/types'
import type { ProjectDocumentStatusValue } from './project-document.types'

export type ProjectDocumentStatusMetadata = {
    label: string
    value: ProjectDocumentStatusValue
    colors: ThemedStatusColors
}

export type ProjectDocumentStatusMetadataMap = Record<ProjectDocumentStatusValue, ProjectDocumentStatusMetadata>
