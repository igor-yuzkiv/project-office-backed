import type { MaybeRefOrGetter } from 'vue'

export const UserQueryKey = {
    all: ['users'] as const,
    search: (query: MaybeRefOrGetter<string>) => [...UserQueryKey.all, 'search', query] as const,
}

export const ApiTokenQueryKey = {
    all: ['api-tokens'] as const,
}
