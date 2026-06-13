import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createTagRequest } from '../api'
import { TagQueryKey } from '../config'

export function useCreateTagMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createTagRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TagQueryKey.all })
        },
    })
}
