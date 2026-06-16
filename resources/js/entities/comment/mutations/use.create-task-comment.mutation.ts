import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createTaskCommentRequest } from '../api'
import { CommentQueryKey } from '../config'
import type { CreateCommentDto } from '../types'
import { type MaybeRefOrGetter, toValue } from 'vue'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'

export function useCreateTaskCommentMutation(taskId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()
    const toast = useToast()

    return useMutation({
        mutationFn: (data: CreateCommentDto) => createTaskCommentRequest(toValue(taskId), data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: CommentQueryKey.taskComments(taskId) })
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to add comment.')
        },
    })
}
