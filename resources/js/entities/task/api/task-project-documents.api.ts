import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { ProjectDocumentOverviewDto } from '@/entities/project-document/types'

type ProjectDocumentsResponse = { data: ProjectDocumentOverviewDto[] }

export async function fetchTaskProjectDocumentsRequest(
    taskId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<ProjectDocumentOverviewDto> {
    return httpClient
        .get<PaginatedResponse<ProjectDocumentOverviewDto>>(`/tasks/${taskId}/project-documents`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function syncTaskProjectDocumentsRequest(
    taskId: string,
    documentIds: string[]
): Promise<ProjectDocumentsResponse> {
    return httpClient
        .put<ProjectDocumentsResponse>(`/tasks/${taskId}/project-documents`, { document_ids: documentIds })
        .then((res) => res.data)
}
