import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'
import type { TaskSearchParams } from '../types/task.api.types'

export const TaskQueryKey = {
    all: ['tasks'] as const,
    search: (params: MaybeRefOrGetter<TaskSearchParams>) => [...TaskQueryKey.all, 'search', params] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...TaskQueryKey.all, 'detail', id] as const,
}

export const TaskCommentQueryKey = {
    taskComments: (taskId: MaybeRefOrGetter<string>) =>
        ['comments', { commentable_type: 'task', commentable_id: taskId }] as const,
    taskCommentsPaginated: (taskId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>
        [...TaskCommentQueryKey.taskComments(taskId), pagination] as const,
}

export const TaskAttachmentQueryKey = {
    taskAttachments: (taskId: MaybeRefOrGetter<string>) => ['attachments', 'tasks', taskId] as const,
    taskAttachmentsPaginated: (taskId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>
        [...TaskAttachmentQueryKey.taskAttachments(taskId), pagination] as const,
}
