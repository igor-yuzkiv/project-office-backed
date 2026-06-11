import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PagingParams, PromisePaginatedResponse } from '@/shared/types'
import type { SortParams } from '@/shared/sort'
import type { ICreateTaskListInput, ITaskList, IUpdateTaskListInput, TaskListSearchParams } from '../types'

type TaskListResponse = { data: ITaskList }

export async function fetchTaskListsRequest(params?: PagingParams & SortParams): PromisePaginatedResponse<ITaskList> {
    return httpClient.get<PaginatedResponse<ITaskList>>('/task-lists', { params }).then((res) => res.data)
}

export async function fetchTaskListRequest(taskListId: string): Promise<TaskListResponse> {
    return httpClient.get<TaskListResponse>(`/task-lists/${taskListId}`).then((res) => res.data)
}

export async function createTaskListRequest(data: ICreateTaskListInput): Promise<TaskListResponse> {
    return httpClient.post<TaskListResponse>('/task-lists', data).then((res) => res.data)
}

export async function updateTaskListRequest(taskListId: string, data: IUpdateTaskListInput): Promise<TaskListResponse> {
    return httpClient.patch<TaskListResponse>(`/task-lists/${taskListId}`, data).then((res) => res.data)
}

export async function deleteTaskListRequest(taskListId: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/task-lists/${taskListId}`).then((res) => res.data)
}

export async function searchTaskListsRequest(params: TaskListSearchParams): PromisePaginatedResponse<ITaskList> {
    const { query = '', filters = [], ...pagination } = params
    return httpClient
        .post<PaginatedResponse<ITaskList>>('/task-lists/search', { query, filters, ...pagination })
        .then((res) => res.data)
}
