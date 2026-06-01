import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updateProjectRequest } from '../api'
import { ProjectQueryKey } from '../config'
import type { IUpdateProjectInput } from '../types'

export function useUpdateProjectMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ id, data }: { id: string; data: IUpdateProjectInput }) => updateProjectRequest(id, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectQueryKey.all })
        },
    })
}
