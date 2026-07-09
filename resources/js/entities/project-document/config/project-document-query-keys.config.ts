import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'
import type { ProjectDocumentFetchParams, ProjectDocumentTreeFetchParams } from '../types'

export const ProjectDocumentQueryKey = {
    all: ['project-documents'] as const,
    list: (projectId: MaybeRefOrGetter<string>, params?: MaybeRefOrGetter<ProjectDocumentFetchParams>) =>
        [...ProjectDocumentQueryKey.all, 'list', projectId, params] as const,
    detail: (id: MaybeRefOrGetter<string>, params?: MaybeRefOrGetter<ProjectDocumentFetchParams>) =>
        [...ProjectDocumentQueryKey.all, 'detail', id, params] as const,
    tree: (projectId: MaybeRefOrGetter<string>, params?: MaybeRefOrGetter<ProjectDocumentTreeFetchParams>) =>
        [...ProjectDocumentQueryKey.all, 'tree', projectId, params] as const,
}

export const ProjectDocumentCommentQueryKey = {
    documentComments: (documentId: MaybeRefOrGetter<string>) =>
        ['comments', { commentable_type: 'project_document', commentable_id: documentId }] as const,
    documentCommentsPaginated: (documentId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>
        [...ProjectDocumentCommentQueryKey.documentComments(documentId), pagination] as const,
}
