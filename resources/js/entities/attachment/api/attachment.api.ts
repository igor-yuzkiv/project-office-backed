import { httpClient } from '@/shared/api'

export async function deleteAttachmentRequest(id: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/attachments/${id}`).then((res) => res.data)
}

export async function downloadAttachmentRequest(id: string): Promise<Blob> {
    return httpClient.get<Blob>(`/attachments/${id}/download`, { responseType: 'blob' }).then((res) => res.data)
}

export async function getAttachmentTemporaryUrlRequest(id: string): Promise<{ url: string }> {
    return httpClient.get<{ url: string }>(`/attachments/${id}/temporary-url`).then((res) => res.data)
}
