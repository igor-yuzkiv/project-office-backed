import { httpClient } from '@/shared/api'
import type { IAnnotation, SaveAnnotationDto } from '../types'

type AnnotationResponse = { data: IAnnotation }

export async function updateAnnotationRequest(
    annotationId: string,
    data: SaveAnnotationDto
): Promise<AnnotationResponse> {
    return httpClient.patch<AnnotationResponse>(`/annotations/${annotationId}`, data).then((res) => res.data)
}

export async function deleteAnnotationRequest(annotationId: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/annotations/${annotationId}`).then((res) => res.data)
}
