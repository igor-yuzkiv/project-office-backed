import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { ICreateTaskInput, ITask, IUpdateTaskInput, TaskFetchParams, TaskSearchParams } from '../types'

type TaskResponse = { data: ITask }

export async function fetchTasksRequest(params?: TaskFetchParams): PromisePaginatedResponse<ITask> {
    const { include, ...rest } = params ?? {}
    return httpClient
        .get<PaginatedResponse<ITask>>('/tasks', { params: { ...rest, include: include?.join(',') } })
        .then((res) => res.data)
}

export async function searchTasksRequest(params: TaskSearchParams): PromisePaginatedResponse<ITask> {
    const { query = '', filters = [], include, ...pagination } = params
    return httpClient
        .post<PaginatedResponse<ITask>>('/tasks/search', { query, filters, include, ...pagination })
        .then((res) => res.data)
}

export async function fetchTaskRequest(taskId: string): Promise<TaskResponse> {
    return httpClient.get<TaskResponse>(`/tasks/${taskId}`).then((res) => res.data)
}

export async function createTaskRequest(data: ICreateTaskInput): Promise<TaskResponse> {
    return httpClient.post<TaskResponse>('/tasks', data).then((res) => res.data)
}

export async function updateTaskRequest(taskId: string, data: IUpdateTaskInput): Promise<TaskResponse> {
    return httpClient.patch<TaskResponse>(`/tasks/${taskId}`, data).then((res) => res.data)
}

export async function deleteTaskRequest(taskId: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/tasks/${taskId}`).then((res) => res.data)
}
