import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { pinProjectRequest, unpinProjectRequest } from '../api'
import { ProjectQueryKey } from '../config'

// ProjectQueryKey.pinned sits under ProjectQueryKey.all, so one invalidation refreshes both the
// pinned list and every project list or detail carrying is_pinned.
export function usePinProjectMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: pinProjectRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectQueryKey.all })
        },
    })
}

export function useUnpinProjectMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: unpinProjectRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectQueryKey.all })
        },
    })
}
