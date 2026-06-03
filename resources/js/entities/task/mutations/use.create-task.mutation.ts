import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createTaskRequest } from '../api'
import { TaskQueryKey } from '../config'

export function useCreateTaskMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createTaskRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
        },
    })
}
