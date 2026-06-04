import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IUpdateTaskInput } from '../types'
import { updateTaskRequest } from '../api'
import { TaskQueryKey } from '../config'

export function useUpdateTaskMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ taskId, data }: { taskId: string; data: IUpdateTaskInput }) => updateTaskRequest(taskId, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
        },
    })
}
