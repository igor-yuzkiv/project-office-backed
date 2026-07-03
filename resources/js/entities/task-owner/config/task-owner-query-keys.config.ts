import type { MaybeRefOrGetter } from 'vue'

export const TaskOwnerQueryKey = {
    all: ['task-owners'] as const,
    list: (taskId: MaybeRefOrGetter<string>) => [...TaskOwnerQueryKey.all, 'list', taskId] as const,
}
