import type { MaybeRefOrGetter } from 'vue'
import type { TaskListSearchParams } from '../types'

export const TaskListQueryKey = {
    all: ['task-lists'] as const,
    search: (params: MaybeRefOrGetter<TaskListSearchParams>) => [...TaskListQueryKey.all, 'search', params] as const,
}
