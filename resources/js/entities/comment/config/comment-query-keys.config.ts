import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'

export const CommentQueryKey = {
    all: ['comments'] as const,
    taskComments: (taskId: MaybeRefOrGetter<string>) => {
        return [...CommentQueryKey.all, { commentable_type: 'task', commentable_id: taskId }] as const
    },
    taskCommentsPaginated: (taskId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) => {
        return [...CommentQueryKey.taskComments(taskId), pagination] as const
    },
}
