import type { ThemedStatusColors } from '@/shared/types'

export type ProjectStatusValue = 'draft' | 'active' | 'on_hold' | 'completed' | 'archived' | 'declined'

export type ProjectStatusMetadata = {
    label: string
    value: ProjectStatusValue
    colors: ThemedStatusColors
}

export type ProjectStatusMetadataMap = Record<ProjectStatusValue, ProjectStatusMetadata>
