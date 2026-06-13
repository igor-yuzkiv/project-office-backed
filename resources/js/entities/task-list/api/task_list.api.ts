import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type {
    ICreateTaskListInput,
    ITaskList,
    IUpdateTaskListInput,
    TaskListFetchParams,
    TaskListSearchParams,
} from '../types'

type TaskListResponse = { data: ITaskList }

export async function fetchTaskListsRequest(params?: TaskListFetchParams): PromisePaginatedResponse<ITaskList> {
    const { include, ...rest } = params ?? {}
    return httpClient
        .get<PaginatedResponse<ITaskList>>('/task-lists', { params: { ...rest, include: include?.join(',') } })
        .then((res) => res.data)
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
    const { query = '', filters = [], include, ...pagination } = params
    return httpClient
        .post<PaginatedResponse<ITaskList>>('/task-lists/search', { query, filters, include, ...pagination })
        .then((res) => res.data)
}
