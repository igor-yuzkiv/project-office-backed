import type { HexColor } from '@/shared/types'

export type ProjectStatusValue = 'draft' | 'active' | 'on_hold' | 'completed' | 'archived'

export type ProjectStatusMetadata = {
    label: string
    value: ProjectStatusValue
    color: HexColor
}

export type ProjectStatusMetadataMap = Record<ProjectStatusValue, ProjectStatusMetadata>
