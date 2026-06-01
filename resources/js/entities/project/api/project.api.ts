import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PagingParams, PromisePaginatedResponse } from '@/shared/types'
import type { SortParams } from '@/shared/sort'
import type { ICreateProjectInput, IProject, IUpdateProjectInput, ProjectSearchParams } from '../types'

type ProjectResponse = { data: IProject }

export async function fetchProjectsRequest(params?: PagingParams & SortParams): PromisePaginatedResponse<IProject> {
    return httpClient.get<PaginatedResponse<IProject>>('/projects', { params }).then((res) => res.data)
}

export async function searchProjectsRequest(params: ProjectSearchParams): PromisePaginatedResponse<IProject> {
    const { query = '', filters = [], ...pagination } = params
    return httpClient
        .post<PaginatedResponse<IProject>>('/projects/search', { query, filters, ...pagination })
        .then((res) => res.data)
}

export async function fetchProjectRequest(id: string): Promise<ProjectResponse> {
    return httpClient.get<ProjectResponse>(`/projects/${id}`).then((res) => res.data)
}

export async function createProjectRequest(data: ICreateProjectInput): Promise<ProjectResponse> {
    return httpClient.post<ProjectResponse>('/projects', data).then((res) => res.data)
}

export async function updateProjectRequest(id: string, data: IUpdateProjectInput): Promise<ProjectResponse> {
    return httpClient.patch<ProjectResponse>(`/projects/${id}`, data).then((res) => res.data)
}

export async function deleteProjectRequest(id: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/projects/${id}`).then((res) => res.data)
}
