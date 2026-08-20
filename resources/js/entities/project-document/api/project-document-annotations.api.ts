import { httpClient } from '@/shared/api'
import type { IAnnotation, SaveAnnotationDto } from '@/entities/annotation/types'

type AnnotationResponse = { data: IAnnotation }
type AnnotationListResponse = { data: IAnnotation[] }

export async function fetchProjectDocumentAnnotationsRequest(documentId: string): Promise<AnnotationListResponse> {
    return httpClient
        .get<AnnotationListResponse>(`/project-documents/${documentId}/annotations`)
        .then((res) => res.data)
}

export async function createProjectDocumentAnnotationRequest(
    documentId: string,
    data: SaveAnnotationDto
): Promise<AnnotationResponse> {
    return httpClient
        .post<AnnotationResponse>(`/project-documents/${documentId}/annotations`, data)
        .then((res) => res.data)
}
