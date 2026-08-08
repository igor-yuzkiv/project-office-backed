import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { CreateCommentDto } from '@/entities/comment/types'
import { createTaskListCommentRequest } from '../api'
import { TaskListCommentQueryKey } from '../config'

export function useCreateTaskListCommentMutation(taskListId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: CreateCommentDto) => createTaskListCommentRequest(toValue(taskListId), data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskListCommentQueryKey.taskListComments(taskListId) })
        },
    })
}
