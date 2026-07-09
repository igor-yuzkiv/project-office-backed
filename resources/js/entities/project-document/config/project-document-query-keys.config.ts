import type { MaybeRefOrGetter } from 'vue'
import type { ProjectDocumentFetchParams, ProjectDocumentTreeFetchParams } from '../types'

export const ProjectDocumentQueryKey = {
    all: ['project-documents'] as const,
    list: (projectId: MaybeRefOrGetter<string>, params?: MaybeRefOrGetter<ProjectDocumentFetchParams>) =>
        [...ProjectDocumentQueryKey.all, 'list', projectId, params] as const,
    detail: (id: MaybeRefOrGetter<string>) => [...ProjectDocumentQueryKey.all, 'detail', id] as const,
    tree: (projectId: MaybeRefOrGetter<string>, params?: MaybeRefOrGetter<ProjectDocumentTreeFetchParams>) =>
        [...ProjectDocumentQueryKey.all, 'tree', projectId, params] as const,
}
