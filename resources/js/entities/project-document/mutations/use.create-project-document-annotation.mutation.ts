import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { SaveAnnotationDto } from '@/entities/annotation/types'
import { createProjectDocumentAnnotationRequest } from '../api'
import { ProjectDocumentAnnotationQueryKey } from '../config'

export function useCreateProjectDocumentAnnotationMutation(documentId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: SaveAnnotationDto) => createProjectDocumentAnnotationRequest(toValue(documentId), data),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: ProjectDocumentAnnotationQueryKey.documentAnnotations(documentId),
            })
        },
    })
}
