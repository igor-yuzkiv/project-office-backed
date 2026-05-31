import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createProjectRequest } from '../api'
import { ProjectQueryKey } from '../config'

export function useCreateProjectMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createProjectRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectQueryKey.all })
        },
    })
}
