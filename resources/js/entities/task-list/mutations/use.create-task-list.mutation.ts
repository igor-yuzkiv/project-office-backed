import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createTaskListRequest } from '../api'
import { TaskListQueryKey } from '../config'

export function useCreateTaskListMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createTaskListRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
        },
    })
}
