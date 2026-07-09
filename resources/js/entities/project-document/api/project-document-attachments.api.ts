import { httpClient } from '@/shared/api'
import type { IAttachment, AttachmentRole } from '@/entities/attachment/types'

type AttachmentResponse = { data: IAttachment }

export async function uploadProjectDocumentAttachmentRequest(
    documentId: string,
    file: File,
    role?: AttachmentRole
): Promise<AttachmentResponse> {
    const formData = new FormData()
    formData.append('file', file)
    if (role !== undefined) formData.append('role', role)

    return httpClient
        .post<AttachmentResponse>(`/project-documents/${documentId}/attachments`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then((res) => res.data)
}
