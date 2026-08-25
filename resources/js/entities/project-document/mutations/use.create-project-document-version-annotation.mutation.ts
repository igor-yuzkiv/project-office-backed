import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { SaveAnnotationDto } from '@/entities/annotation/types'
import { createProjectDocumentVersionAnnotationRequest } from '../api'
import { ProjectDocumentVersionAnnotationQueryKey } from '../config'

export function useCreateProjectDocumentVersionAnnotationMutation(versionId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: SaveAnnotationDto) =>
            createProjectDocumentVersionAnnotationRequest(toValue(versionId), data),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: ProjectDocumentVersionAnnotationQueryKey.versionAnnotations(versionId),
            })
        },
    })
}
