import { httpClient } from '@/shared/api'
import type { IProjectDocumentVersionsResponse } from '../types'

export async function fetchProjectDocumentVersionsRequest(
    documentId: string
): Promise<IProjectDocumentVersionsResponse> {
    return httpClient
        .get<IProjectDocumentVersionsResponse>(`/project-documents/${documentId}/versions`)
        .then((res) => res.data)
}
