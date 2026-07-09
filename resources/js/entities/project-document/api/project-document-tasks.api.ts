import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { TaskOverviewDto } from '@/entities/task/types'

type TasksResponse = { data: TaskOverviewDto[] }

export async function fetchProjectDocumentTasksRequest(
    documentId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<TaskOverviewDto> {
    return httpClient
        .get<PaginatedResponse<TaskOverviewDto>>(`/project-documents/${documentId}/tasks`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

export async function syncProjectDocumentTasksRequest(documentId: string, taskIds: string[]): Promise<TasksResponse> {
    return httpClient
        .put<TasksResponse>(`/project-documents/${documentId}/tasks`, { task_ids: taskIds })
        .then((res) => res.data)
}
