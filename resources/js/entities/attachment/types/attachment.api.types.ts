import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { ModuleName } from '@/shared/types'
import type { AttachmentRole } from './attachment.types'

export type AttachmentInclude = 'createdBy' | 'updatedBy'

export interface IUploadAttachmentInput {
    file: File
    entity_type?: ModuleName
    entity_id?: string
    role?: AttachmentRole
}

export type AttachmentSearchParams = SortParams & {
    filters?: FilterPayloadItem[]
    page?: number
    per_page?: number
    include?: AttachmentInclude[]
}
