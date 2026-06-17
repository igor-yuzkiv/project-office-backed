import type { IEntity } from '@/shared/types'
import type { UserOverviewDto } from '@/entities/user/types'

export type AttachmentRole = string

export interface IAttachment extends IEntity {
    url: string
    original_name: string
    extension: string | null
    mime_type: string | null
    size_bytes: number | null
    storage_provider: string
    storage_key: string
    role: AttachmentRole | null

    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
}
