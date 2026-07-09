import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type {
    ICreateProjectDocumentInput,
    IProjectDocumentResponse,
    IProjectDocumentsResponse,
    IUpdateProjectDocumentInput,
    ProjectDocumentFetchParams,
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

export async function fetchProjectDocumentRequest(id: string): Promise<IProjectDocumentResponse> {
    return httpClient.get<IProjectDocumentResponse>(`/project-documents/${id}`).then((res) => res.data)
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
