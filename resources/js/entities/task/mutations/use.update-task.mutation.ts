import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IUpdateTaskInput } from '../types'
import { updateTaskRequest } from '../api'
import { TaskListQueryKey } from '@/entities/task-list/config'
import { TaskQueryKey } from '../config'

export function useUpdateTaskMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ taskId, data }: { taskId: string; data: IUpdateTaskInput }) => updateTaskRequest(taskId, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
            // A task's status shows up in its list's sidebar panel and in the tally above it.
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
        },
    })
}
