import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { TaskOverviewDto } from '@/entities/task/types'
import type { IAddTasksToTaskListInput } from '../types'

type AddedTasksResponse = { data: TaskOverviewDto[] }

/** Ordered by name on the server — the list's own numbering is the order it is read in. */
export async function fetchTaskListTasksRequest(
    taskListId: string,
    page?: number,
    perPage?: number
): PromisePaginatedResponse<TaskOverviewDto> {
    return httpClient
        .get<PaginatedResponse<TaskOverviewDto>>(`/task-lists/${taskListId}/tasks`, {
            params: { page, per_page: perPage },
        })
        .then((res) => res.data)
}

/** Adds only — tasks already belonging to a list are rejected by the backend, never moved. */
export async function addTasksToTaskListRequest(
    taskListId: string,
    data: IAddTasksToTaskListInput
): Promise<AddedTasksResponse> {
    return httpClient.post<AddedTasksResponse>(`/task-lists/${taskListId}/tasks`, data).then((res) => res.data)
}
