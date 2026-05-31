import type { IEntity } from '@/shared/types'

export interface IAttachment extends IEntity {
    original_name: string
    extension: string | null
    mime_type: string | null
    size_bytes: number | null
    storage_provider: string
    storage_key: string
    entity_type: string | null
    entity_id: string | null
    role: string | null
}

export interface IUploadAttachmentInput {
    file: File
    entity_type?: string
    entity_id?: string
    role?: string
}
