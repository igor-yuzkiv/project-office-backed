import type { MaybeRefOrGetter } from 'vue'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'
import { useUpdateCommentMutation } from '@/entities/comment'
import { useCreateTaskCommentMutation } from '../mutations/use.create-task-comment.mutation'

type UpsertPayload =
    | { mode: 'create'; content: string }
    | { mode: 'edit'; commentId: string; content: string }

export function useUpsertTaskComment(taskId: MaybeRefOrGetter<string>) {
    const toast = useToast()
    const createMutation = useCreateTaskCommentMutation(taskId)
    const updateMutation = useUpdateCommentMutation()

    async function upsert(payload: UpsertPayload) {
        try {
            console.log('payload', { taskId, payload })

            if (payload.mode === 'create') {
                await createMutation.mutateAsync({ content: payload.content })
            } else {
                await updateMutation.mutateAsync({ commentId: payload.commentId, data: { content: payload.content } })
            }
        } catch (error) {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save comment.')
        }
    }

    return { upsert }
}
