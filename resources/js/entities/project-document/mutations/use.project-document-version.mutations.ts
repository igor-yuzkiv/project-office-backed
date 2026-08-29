import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
    createProjectDocumentVersionRequest,
    deleteProjectDocumentVersionRequest,
    setProjectDocumentPrimaryVersionRequest,
    updateProjectDocumentVersionRequest,
} from '../api'
import { ProjectDocumentQueryKey, ProjectDocumentVersionQueryKey } from '../config'
import type {
    ICreateProjectDocumentVersionInput,
    ISetProjectDocumentPrimaryVersionInput,
    IUpdateProjectDocumentVersionInput,
} from '../types'

/**
 * Every version write also changes what the document itself reports as its content, so both the
 * version list and the document detail are invalidated together. Returned from `onSuccess`, so
 * `mutateAsync` settles only once the list is fresh.
 */
export function useVersionMutationInvalidation() {
    const queryClient = useQueryClient()

    return (documentId: string) =>
        Promise.all([
            queryClient.invalidateQueries({ queryKey: ProjectDocumentVersionQueryKey.documentVersions(documentId) }),
            // `all` is a prefix of every document key, so the open document's detail refreshes with it.
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all }),
        ])
}

export function useCreateProjectDocumentVersionMutation() {
    const invalidate = useVersionMutationInvalidation()

    return useMutation({
        mutationFn: ({ documentId, data }: { documentId: string; data: ICreateProjectDocumentVersionInput }) =>
            createProjectDocumentVersionRequest(documentId, data),
        onSuccess: (_result, { documentId }) => invalidate(documentId),
    })
}

export function useUpdateProjectDocumentVersionMutation() {
    const invalidate = useVersionMutationInvalidation()

    return useMutation({
        mutationFn: ({ versionId, data }: { documentId: string; versionId: string; data: IUpdateProjectDocumentVersionInput }) =>
            updateProjectDocumentVersionRequest(versionId, data),
        onSuccess: (_result, { documentId }) => invalidate(documentId),
    })
}

export function useDeleteProjectDocumentVersionMutation() {
    const invalidate = useVersionMutationInvalidation()

    return useMutation({
        mutationFn: ({ versionId }: { documentId: string; versionId: string }) =>
            deleteProjectDocumentVersionRequest(versionId),
        onSuccess: (_result, { documentId }) => invalidate(documentId),
    })
}

export function useSetProjectDocumentPrimaryVersionMutation() {
    const invalidate = useVersionMutationInvalidation()

    return useMutation({
        mutationFn: ({ documentId, data }: { documentId: string; data: ISetProjectDocumentPrimaryVersionInput }) =>
            setProjectDocumentPrimaryVersionRequest(documentId, data),
        onSuccess: (_result, { documentId }) => invalidate(documentId),
    })
}
