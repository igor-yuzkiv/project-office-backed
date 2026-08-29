import { httpClient } from '@/shared/api'
import type {
    ICreateProjectDocumentVersionInput,
    IProjectDocumentResponse,
    IProjectDocumentVersionResponse,
    IProjectDocumentVersionsResponse,
    ISetProjectDocumentPrimaryVersionInput,
    IUpdateProjectDocumentVersionContentInput,
    IUpdateProjectDocumentVersionInput,
} from '../types'

export async function fetchProjectDocumentVersionsRequest(
    documentId: string
): Promise<IProjectDocumentVersionsResponse> {
    return httpClient
        .get<IProjectDocumentVersionsResponse>(`/project-documents/${documentId}/versions`)
        .then((res) => res.data)
}

export async function createProjectDocumentVersionRequest(
    documentId: string,
    data: ICreateProjectDocumentVersionInput
): Promise<IProjectDocumentVersionResponse> {
    return httpClient
        .post<IProjectDocumentVersionResponse>(`/project-documents/${documentId}/versions`, data)
        .then((res) => res.data)
}

export async function updateProjectDocumentVersionContentRequest(
    documentId: string,
    versionId: string,
    data: IUpdateProjectDocumentVersionContentInput
): Promise<IProjectDocumentVersionResponse> {
    return httpClient
        .put<IProjectDocumentVersionResponse>(`/project-documents/${documentId}/versions/${versionId}`, data)
        .then((res) => res.data)
}

export async function updateProjectDocumentVersionRequest(
    versionId: string,
    data: IUpdateProjectDocumentVersionInput
): Promise<IProjectDocumentVersionResponse> {
    return httpClient
        .put<IProjectDocumentVersionResponse>(`/project-document-versions/${versionId}`, data)
        .then((res) => res.data)
}

export async function deleteProjectDocumentVersionRequest(versionId: string): Promise<void> {
    await httpClient.delete(`/project-document-versions/${versionId}`)
}

export async function setProjectDocumentPrimaryVersionRequest(
    documentId: string,
    data: ISetProjectDocumentPrimaryVersionInput
): Promise<IProjectDocumentResponse> {
    return httpClient
        .put<IProjectDocumentResponse>(`/project-documents/${documentId}/primary-version`, data)
        .then((res) => res.data)
}
