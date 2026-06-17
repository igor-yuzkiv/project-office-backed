import { httpClient } from '@/shared/api'

export async function deleteAttachmentRequest(id: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/attachments/${id}`).then((res) => res.data)
}
