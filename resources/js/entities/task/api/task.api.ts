import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PagingParams, PromisePaginatedResponse } from '@/shared/types'
import type { SortParams } from '@/shared/sort'
import type { ICreateTaskInput, ITask, IUpdateTaskInput } from '../types'

type TaskResponse = { data: ITask }

export async function fetchTasksRequest(params?: PagingParams & SortParams): PromisePaginatedResponse<ITask> {
    return httpClient.get<PaginatedResponse<ITask>>('/tasks', { params }).then((res) => res.data)
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
