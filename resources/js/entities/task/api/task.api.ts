import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PagingParams, PromisePaginatedResponse } from '@/shared/types'
import type { SortParams } from '@/shared/sort'
import type { ICreateTaskInput, ITask, IUpdateTaskInput } from '../types'

type TaskResponse = { data: ITask }

export async function fetchTasksRequest(
    projectId: string,
    params?: PagingParams & SortParams
): PromisePaginatedResponse<ITask> {
    return httpClient.get<PaginatedResponse<ITask>>(`/projects/${projectId}/tasks`, { params }).then((res) => res.data)
}

export async function fetchTaskRequest(projectId: string, taskId: string): Promise<TaskResponse> {
    return httpClient.get<TaskResponse>(`/projects/${projectId}/tasks/${taskId}`).then((res) => res.data)
}

export async function createTaskRequest(projectId: string, data: ICreateTaskInput): Promise<TaskResponse> {
    return httpClient.post<TaskResponse>(`/projects/${projectId}/tasks`, data).then((res) => res.data)
}

export async function updateTaskRequest(
    projectId: string,
    taskId: string,
    data: IUpdateTaskInput
): Promise<TaskResponse> {
    return httpClient.patch<TaskResponse>(`/projects/${projectId}/tasks/${taskId}`, data).then((res) => res.data)
}

export async function deleteTaskRequest(projectId: string, taskId: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/projects/${projectId}/tasks/${taskId}`).then((res) => res.data)
}
