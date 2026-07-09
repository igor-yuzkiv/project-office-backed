import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'

export function useCreateProjectDocumentMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createProjectDocumentRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
        },
    })
}
