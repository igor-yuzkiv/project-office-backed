import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IMoveProjectDocumentInput } from '../types'
import { moveProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'

export function useMoveProjectDocumentMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ id, data }: { id: string; data: IMoveProjectDocumentInput }) =>
            moveProjectDocumentRequest(id, data),
        onSuccess: (_result, { id }) => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.detail(id) })
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
        },
    })
}
