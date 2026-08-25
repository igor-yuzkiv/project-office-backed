import { httpClient } from '@/shared/api'
import type { IAnnotation, SaveAnnotationDto } from '@/entities/annotation/types'

type AnnotationResponse = { data: IAnnotation }
type AnnotationListResponse = { data: IAnnotation[] }

export async function fetchProjectDocumentVersionAnnotationsRequest(
    versionId: string
): Promise<AnnotationListResponse> {
    return httpClient
        .get<AnnotationListResponse>(`/project-document-versions/${versionId}/annotations`)
        .then((res) => res.data)
}

export async function createProjectDocumentVersionAnnotationRequest(
    versionId: string,
    data: SaveAnnotationDto
): Promise<AnnotationResponse> {
    return httpClient
        .post<AnnotationResponse>(`/project-document-versions/${versionId}/annotations`, data)
        .then((res) => res.data)
}
