import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type {
    ICreateProjectDocumentInput,
    IProjectDocument,
    IProjectDocumentResponse,
    IProjectDocumentsResponse,
    IUpdateProjectDocumentInput,
    ProjectDocumentFetchParams,
    ProjectDocumentOverviewDto,
    ProjectDocumentSearchParams,
    ProjectDocumentTreeFetchParams,
    ProjectDocumentTreeNodeDto,
} from '../types'

export async function fetchProjectDocumentsRequest(
    projectId: string,
    params?: ProjectDocumentFetchParams
): Promise<IProjectDocumentsResponse> {
    const { include } = params ?? {}
    return httpClient
        .get<IProjectDocumentsResponse>(`/projects/${projectId}/project-documents`, {
            params: { include: include?.join(',') },
        })
        .then((res) => res.data)
}

export async function searchProjectDocumentsRequest(
    params: ProjectDocumentSearchParams
): PromisePaginatedResponse<ProjectDocumentOverviewDto> {
    const { query = '', filters = [], include, ...pagination } = params
    return httpClient
        .post<PaginatedResponse<IProjectDocument>>('/project-documents/search', {
            query,
            filters,
            include,
            ...pagination,
        })
        .then((res) => res.data)
}

export async function fetchProjectDocumentRequest(
    id: string,
    params?: ProjectDocumentFetchParams
): Promise<IProjectDocumentResponse> {
    return httpClient.get<IProjectDocumentResponse>(`/project-documents/${id}`, { params }).then((res) => res.data)
}

export async function createProjectDocumentRequest(
    data: ICreateProjectDocumentInput
): Promise<IProjectDocumentResponse> {
    return httpClient
        .post<IProjectDocumentResponse>(`/projects/${data.project_id}/project-documents`, data)
        .then((res) => res.data)
}

export async function updateProjectDocumentRequest(
    id: string,
    data: IUpdateProjectDocumentInput
): Promise<IProjectDocumentResponse> {
    return httpClient.put<IProjectDocumentResponse>(`/project-documents/${id}`, data).then((res) => res.data)
}

export async function deleteProjectDocumentRequest(id: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/project-documents/${id}`).then((res) => res.data)
}

export async function fetchProjectDocumentTreeRequest(
    projectId: string,
    params?: ProjectDocumentTreeFetchParams
): PromisePaginatedResponse<ProjectDocumentTreeNodeDto> {
    return httpClient
        .get<PaginatedResponse<ProjectDocumentTreeNodeDto>>(`/projects/${projectId}/project-documents/tree`, {
            params,
        })
        .then((res) => res.data)
}
