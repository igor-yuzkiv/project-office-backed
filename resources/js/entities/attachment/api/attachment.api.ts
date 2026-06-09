import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { IAttachment, IUploadAttachmentInput, AttachmentSearchParams } from '../types'

type AttachmentResponse = { data: IAttachment }

export async function searchAttachmentsRequest(params: AttachmentSearchParams): PromisePaginatedResponse<IAttachment> {
    const { filters = [], include, ...pagination } = params
    return httpClient
        .post<PaginatedResponse<IAttachment>>('/attachments/search', { filters, include, ...pagination })
        .then((res) => res.data)
}

export async function uploadAttachmentRequest(input: IUploadAttachmentInput): Promise<AttachmentResponse> {
    const formData = new FormData()
    formData.append('file', input.file)
    if (input.entity_type !== undefined) formData.append('entity_type', input.entity_type)
    if (input.entity_id !== undefined) formData.append('entity_id', input.entity_id)
    if (input.role !== undefined) formData.append('role', input.role)

    return httpClient
        .post<AttachmentResponse>('/attachments', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then((res) => res.data)
}
