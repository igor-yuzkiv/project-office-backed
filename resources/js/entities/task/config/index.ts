import type { MaybeRefOrGetter } from 'vue'
import type { TaskSearchParams } from '../types'

export const TaskQueryKey = {
    all: ['tasks'] as const,
    search: (params: MaybeRefOrGetter<TaskSearchParams>) => [...TaskQueryKey.all, 'search', params] as const,
}
