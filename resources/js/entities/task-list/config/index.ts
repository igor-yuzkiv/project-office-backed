import type { MaybeRefOrGetter } from 'vue'
import type { TaskListSearchParams } from '../types'

export * from './task-list-status.config'
export * from './task-list-views.config'
export * from './task-list-attachment.config'

export const TaskListQueryKey = {
    all: ['task-lists'] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...TaskListQueryKey.all, 'detail', id] as const,
    search: (params: MaybeRefOrGetter<TaskListSearchParams>) => [...TaskListQueryKey.all, 'search', params] as const,
}
