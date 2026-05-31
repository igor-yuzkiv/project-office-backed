import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'

export const ProjectQueryKey = {
    all: ['projects'] as const,
    paginated: (params: MaybeRefOrGetter<PagingParams>) =>
        [...ProjectQueryKey.all, 'paginated', params] as const,
}
