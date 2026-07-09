import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IUpdateProjectDocumentInput } from '../types'
import { updateProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'

export function useUpdateProjectDocumentMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ id, data }: { id: string; data: IUpdateProjectDocumentInput }) =>
            updateProjectDocumentRequest(id, data),
        onSuccess: (_result, { id }) => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.detail(id) })
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
        },
    })
}
