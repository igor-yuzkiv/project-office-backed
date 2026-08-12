import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IBulkUpdateTaskStatusInput } from '../types'
import { bulkUpdateTaskStatusRequest } from '../api'
import { TaskListQueryKey } from '@/entities/task-list/config'
import { TaskQueryKey } from '../config'

export function useBulkUpdateTaskStatusMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: IBulkUpdateTaskStatusInput) => bulkUpdateTaskStatusRequest(data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
            // A task's status shows up in its list's sidebar panel and in the tally above it.
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
        },
    })
}
