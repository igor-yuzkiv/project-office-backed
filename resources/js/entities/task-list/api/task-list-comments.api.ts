import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { IComment, CreateCommentDto } from '@/entities/comment/types'

type CommentResponse = { data: IComment }

export async function fetchTaskListCommentsRequest(
    taskListId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<IComment> {
    return httpClient
        .get<PaginatedResponse<IComment>>(`/task-lists/${taskListId}/comments`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function createTaskListCommentRequest(
    taskListId: string,
    data: CreateCommentDto
): Promise<CommentResponse> {
    return httpClient.post<CommentResponse>(`/task-lists/${taskListId}/comments`, data).then((res) => res.data)
}
