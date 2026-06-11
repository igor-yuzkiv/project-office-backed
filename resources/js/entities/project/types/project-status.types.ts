import type { HexColor } from '@/shared/types'

export type ProjectStatusValue = 'draft' | 'active' | 'inactive' | 'archived' | 'completed' | 'on_hold' | 'cancelled'

export type ProjectStatusMetadata = {
    label: string
    value: ProjectStatusValue
    color: HexColor
}

export type ProjectStatusMetadataMap = Record<ProjectStatusValue, ProjectStatusMetadata>
