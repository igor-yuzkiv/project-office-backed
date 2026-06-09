import type { IEntity, ModuleName } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'

export interface IAttachment extends IEntity {
    url: string
    original_name: string
    extension: string | null
    mime_type: string | null
    size_bytes: number | null
    storage_provider: string
    storage_key: string
    entity_type: ModuleName | null
    entity_id: string | null
    role: string | null
}

export interface IUploadAttachmentInput {
    file: File
    entity_type?: ModuleName
    entity_id?: string
    role?: string
}

export type AttachmentInclude = 'createdBy' | 'updatedBy'

export type AttachmentSearchParams = SortParams & {
    filters?: FilterPayloadItem[]
    page?: number
    per_page?: number
    include?: AttachmentInclude[]
}
