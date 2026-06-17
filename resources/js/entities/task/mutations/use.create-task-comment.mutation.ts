import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { CreateCommentDto } from '@/entities/comment/types'
import { createTaskCommentRequest } from '../api'
import { TaskCommentQueryKey } from '../config'

export function useCreateTaskCommentMutation(taskId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: CreateCommentDto) => createTaskCommentRequest(toValue(taskId), data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskCommentQueryKey.taskComments(taskId) })
        },
    })
}
