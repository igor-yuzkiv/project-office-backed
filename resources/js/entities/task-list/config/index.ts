import type { MaybeRefOrGetter } from 'vue'
import type { TaskListSearchParams } from '../types'

export * from './task-list-status.config'
export * from './task-list-views.config'

export const TaskListQueryKey = {
    all: ['task-lists'] as const,
    search: (params: MaybeRefOrGetter<TaskListSearchParams>) => [...TaskListQueryKey.all, 'search', params] as const,
}
