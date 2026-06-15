import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type {
    ICreateProjectInput,
    IProject,
    IUpdateProjectInput,
    ProjectFetchParams,
    ProjectOverviewDto,
    ProjectSearchParams,
} from '../types'

type ProjectResponse = { data: IProject }

export async function fetchProjectsRequest(params?: ProjectFetchParams): PromisePaginatedResponse<ProjectOverviewDto> {
    const { include, ...rest } = params ?? {}
    return httpClient
        .get<PaginatedResponse<ProjectOverviewDto>>('/projects', { params: { ...rest, include: include?.join(',') } })
        .then((res) => res.data)
}

export async function searchProjectsRequest(params: ProjectSearchParams): PromisePaginatedResponse<ProjectOverviewDto> {
    const { query = '', filters = [], include, ...pagination } = params
    return httpClient
        .post<PaginatedResponse<ProjectOverviewDto>>('/projects/search', { query, filters, include, ...pagination })
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
