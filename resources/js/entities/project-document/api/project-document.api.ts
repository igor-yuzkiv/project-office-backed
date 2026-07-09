import { httpClient } from '@/shared/api'
import type {
    ICreateProjectDocumentInput,
    IProjectDocumentResponse,
    IProjectDocumentsResponse,
    IUpdateProjectDocumentInput,
    ProjectDocumentFetchParams,
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
