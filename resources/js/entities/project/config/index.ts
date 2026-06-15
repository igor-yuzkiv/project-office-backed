import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'
import type { ProjectSearchParams } from '../types'

export const ProjectQueryKey = {
    all: ['projects'] as const,
    paginated: (params: MaybeRefOrGetter<PagingParams>) => [...ProjectQueryKey.all, 'paginated', params] as const,
    search: (params: MaybeRefOrGetter<ProjectSearchParams>) => [...ProjectQueryKey.all, 'search', params] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...ProjectQueryKey.all, 'detail', id] as const,
}

export * from './project-attachment.config'
export * from './project-module.config'
export * from './project-status.config'
