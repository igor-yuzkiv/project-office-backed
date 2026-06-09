import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IUpdateTaskListInput } from '../types'
import { updateTaskListRequest } from '../api'
import { TaskListQueryKey } from '../config'

export function useUpdateTaskListMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ taskListId, data }: { taskListId: string; data: IUpdateTaskListInput }) =>
            updateTaskListRequest(taskListId, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
        },
    })
}
