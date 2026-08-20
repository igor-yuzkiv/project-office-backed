import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updateAnnotationRequest } from '../api'
import { AnnotationQueryKey } from '../config'
import type { SaveAnnotationDto } from '../types'

export function useUpdateAnnotationMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ annotationId, data }: { annotationId: string; data: SaveAnnotationDto }) =>
            updateAnnotationRequest(annotationId, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: AnnotationQueryKey.all })
        },
    })
}
