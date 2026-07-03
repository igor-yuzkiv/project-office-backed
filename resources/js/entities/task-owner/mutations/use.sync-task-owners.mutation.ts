import { type MaybeRefOrGetter, toValue } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { syncTaskOwnersRequest } from '../api'
import { TaskOwnerQueryKey } from '../config'
import type { SyncTaskOwnersPayload } from '../types'

export function useSyncTaskOwnersMutation(taskId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (payload: SyncTaskOwnersPayload) => syncTaskOwnersRequest(toValue(taskId), payload),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskOwnerQueryKey.list(taskId) })
        },
    })
}
