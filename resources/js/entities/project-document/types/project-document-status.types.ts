import type { HexColor } from '@/shared/types'
import type { ProjectDocumentStatusValue } from './project-document.types'

export type ProjectDocumentStatusMetadata = {
    label: string
    value: ProjectDocumentStatusValue
    color: HexColor
}

export type ProjectDocumentStatusMetadataMap = Record<ProjectDocumentStatusValue, ProjectDocumentStatusMetadata>
