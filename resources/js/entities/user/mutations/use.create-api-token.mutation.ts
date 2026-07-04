import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createApiTokenRequest } from '../api'
import { ApiTokenQueryKey } from '../config'
import type { CreateApiTokenPayload } from '../types'

export function useCreateApiTokenMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (payload: CreateApiTokenPayload) => createApiTokenRequest(payload),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ApiTokenQueryKey.all })
        },
    })
}
