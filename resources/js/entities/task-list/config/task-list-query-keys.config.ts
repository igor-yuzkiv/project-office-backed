import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'
import type { TaskListSearchParams } from '../types'

export const TaskListQueryKey = {
    all: ['task-lists'] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...TaskListQueryKey.all, 'detail', id] as const,
    search: (params: MaybeRefOrGetter<TaskListSearchParams>) => [...TaskListQueryKey.all, 'search', params] as const,
}

export const TaskListCommentQueryKey = {
    taskListComments: (taskListId: MaybeRefOrGetter<string>) =>
        ['comments', { commentable_type: 'task-list', commentable_id: taskListId }] as const,
    taskListCommentsPaginated: (taskListId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>
        [...TaskListCommentQueryKey.taskListComments(taskListId), pagination] as const,
}

export const TaskListAttachmentQueryKey = {
    taskListAttachments: (taskListId: MaybeRefOrGetter<string>) => ['attachments', 'task-lists', taskListId] as const,
    taskListAttachmentsPaginated: (taskListId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>
        [...TaskListAttachmentQueryKey.taskListAttachments(taskListId), pagination] as const,
}
