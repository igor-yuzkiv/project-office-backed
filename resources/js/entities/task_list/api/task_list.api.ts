import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PagingParams, PromisePaginatedResponse, SortParams } from '@/shared/types'
import type { ICreateTaskListInput, ITaskList, IUpdateTaskListInput } from '../types'

type TaskListResponse = { data: ITaskList }

export async function fetchTaskListsRequest(
    projectId: string,
    params?: PagingParams & SortParams
): PromisePaginatedResponse<ITaskList> {
    return httpClient
        .get<PaginatedResponse<ITaskList>>(`/projects/${projectId}/task-lists`, { params })
        .then((res) => res.data)
}

export async function fetchTaskListRequest(projectId: string, taskListId: string): Promise<TaskListResponse> {
    return httpClient.get<TaskListResponse>(`/projects/${projectId}/task-lists/${taskListId}`).then((res) => res.data)
}

export async function createTaskListRequest(projectId: string, data: ICreateTaskListInput): Promise<TaskListResponse> {
    return httpClient.post<TaskListResponse>(`/projects/${projectId}/task-lists`, data).then((res) => res.data)
}

export async function updateTaskListRequest(
    projectId: string,
    taskListId: string,
    data: IUpdateTaskListInput
): Promise<TaskListResponse> {
    return httpClient
        .patch<TaskListResponse>(`/projects/${projectId}/task-lists/${taskListId}`, data)
        .then((res) => res.data)
}

export async function deleteTaskListRequest(projectId: string, taskListId: string): Promise<{ message: string }> {
    return httpClient
        .delete<{ message: string }>(`/projects/${projectId}/task-lists/${taskListId}`)
        .then((res) => res.data)
}
