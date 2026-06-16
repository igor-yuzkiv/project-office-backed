import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { IComment, CreateCommentDto, UpdateCommentDto } from '../types'

type CommentResponse = { data: IComment }

export async function fetchTaskCommentsRequest(
    taskId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<IComment> {
    return httpClient
        .get<PaginatedResponse<IComment>>(`/tasks/${taskId}/comments`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function createTaskCommentRequest(taskId: string, data: CreateCommentDto): Promise<CommentResponse> {
    return httpClient.post<CommentResponse>(`/tasks/${taskId}/comments`, data).then((res) => res.data)
}

export async function updateCommentRequest(commentId: string, data: UpdateCommentDto): Promise<CommentResponse> {
    return httpClient.patch<CommentResponse>(`/comments/${commentId}`, data).then((res) => res.data)
}

export async function deleteCommentRequest(commentId: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/comments/${commentId}`).then((res) => res.data)
}
