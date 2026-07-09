import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { IComment, CreateCommentDto } from '@/entities/comment/types'

type CommentResponse = { data: IComment }

export async function fetchProjectDocumentCommentsRequest(
    documentId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<IComment> {
    return httpClient
        .get<PaginatedResponse<IComment>>(`/project-documents/${documentId}/comments`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function createProjectDocumentCommentRequest(
    documentId: string,
    data: CreateCommentDto
): Promise<CommentResponse> {
    return httpClient.post<CommentResponse>(`/project-documents/${documentId}/comments`, data).then((res) => res.data)
}
