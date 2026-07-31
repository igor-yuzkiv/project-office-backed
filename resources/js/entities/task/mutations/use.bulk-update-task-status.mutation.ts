import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { IBulkUpdateTaskStatusInput } from '../types'
import { bulkUpdateTaskStatusRequest } from '../api'
import { TaskQueryKey } from '../config'

export function useBulkUpdateTaskStatusMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: IBulkUpdateTaskStatusInput) => bulkUpdateTaskStatusRequest(data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
        },
    })
}
