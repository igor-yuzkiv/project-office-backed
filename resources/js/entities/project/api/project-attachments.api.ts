import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { IAttachment, AttachmentRole } from '@/entities/attachment/types'

type AttachmentResponse = { data: IAttachment }

export async function fetchProjectAttachmentsRequest(
    projectId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<IAttachment> {
    return httpClient
        .get<PaginatedResponse<IAttachment>>(`/projects/${projectId}/attachments`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function uploadProjectAttachmentRequest(
    projectId: string,
    file: File,
    role?: AttachmentRole
): Promise<AttachmentResponse> {
    const formData = new FormData()
    formData.append('file', file)
    if (role !== undefined) formData.append('role', role)

    return httpClient
        .post<AttachmentResponse>(`/projects/${projectId}/attachments`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then((res) => res.data)
}
