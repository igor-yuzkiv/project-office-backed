import type { MaybeRefOrGetter } from 'vue'

export const TagQueryKey = {
    all: ['tags'] as const,
    search: (query: MaybeRefOrGetter<string>) => [...TagQueryKey.all, 'search', query] as const,
}
