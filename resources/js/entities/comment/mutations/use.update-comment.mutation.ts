import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updateCommentRequest } from '../api'
import { CommentQueryKey } from '../config'
import type { UpdateCommentDto } from '../types'

export function useUpdateCommentMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ commentId, data }: { commentId: string; data: UpdateCommentDto }) =>
            updateCommentRequest(commentId, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: CommentQueryKey.all })
        },
    })
}
