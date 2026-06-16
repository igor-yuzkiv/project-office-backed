import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteCommentRequest } from '../api'
import { CommentQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'

export function useDeleteCommentMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()
    const toast = useToast()

    const { mutate, ...rest } = useMutation({
        mutationFn: (commentId: string) => deleteCommentRequest(commentId),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: CommentQueryKey.all })
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to delete comment.')
        },
    })

    async function mutateWithConfirm(commentId: string) {
        const confirmed = await confirm.requireAsync({
            header: 'Delete Comment',
            message: 'Are you sure you want to delete this comment?',
            acceptLabel: 'Delete',
            rejectLabel: 'Cancel',
        })

        if (confirmed) {
            mutate(commentId)
        }
    }

    return { mutate, mutateWithConfirm, ...rest }
}
