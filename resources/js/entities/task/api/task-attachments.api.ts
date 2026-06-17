import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { IAttachment, AttachmentRole } from '@/entities/attachment/types'

type AttachmentResponse = { data: IAttachment }

export async function fetchTaskAttachmentsRequest(
    taskId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<IAttachment> {
    return httpClient
        .get<PaginatedResponse<IAttachment>>(`/tasks/${taskId}/attachments`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function uploadTaskAttachmentRequest(
    taskId: string,
    file: File,
    role?: AttachmentRole
): Promise<AttachmentResponse> {
    const formData = new FormData()
    formData.append('file', file)
    if (role !== undefined) formData.append('role', role)

    return httpClient
        .post<AttachmentResponse>(`/tasks/${taskId}/attachments`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then((res) => res.data)
}
