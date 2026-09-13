import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createTaskRequest } from '../api'
import { TaskQueryKey } from '../config'
import { TaskListQueryKey } from '@/entities/task-list/config'
import { TaskViewQueryKey } from '@/entities/task-view/config'

export function useCreateTaskMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createTaskRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
            queryClient.invalidateQueries({ queryKey: TaskViewQueryKey.all })
        },
    })
}
