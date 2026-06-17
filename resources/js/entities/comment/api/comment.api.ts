import { httpClient } from '@/shared/api'
import type { IComment, UpdateCommentDto } from '../types'

type CommentResponse = { data: IComment }

export async function updateCommentRequest(commentId: string, data: UpdateCommentDto): Promise<CommentResponse> {
    return httpClient.patch<CommentResponse>(`/comments/${commentId}`, data).then((res) => res.data)
}

export async function deleteCommentRequest(commentId: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/comments/${commentId}`).then((res) => res.data)
}
