import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updateCommentRequest } from '../api'
import { CommentQueryKey } from '../config'
import type { UpdateCommentDto } from '../types'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'

export function useUpdateCommentMutation() {
    const queryClient = useQueryClient()
    const toast = useToast()

    return useMutation({
        mutationFn: ({ commentId, data }: { commentId: string; data: UpdateCommentDto }) =>
            updateCommentRequest(commentId, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: CommentQueryKey.all })
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to update comment.')
        },
    })
}
