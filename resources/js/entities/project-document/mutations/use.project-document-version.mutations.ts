import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
    createProjectDocumentVersionRequest,
    deleteProjectDocumentVersionRequest,
    setProjectDocumentPrimaryVersionRequest,
    updateProjectDocumentVersionsRequest,
} from '../api'
import { ProjectDocumentQueryKey, ProjectDocumentVersionQueryKey } from '../config'
import type {
    ICreateProjectDocumentVersionInput,
    ISetProjectDocumentPrimaryVersionInput,
    IUpdateProjectDocumentVersionsInput,
} from '../types'

/**
 * Every version write also changes what the document itself reports as its content, so both the
 * version list and the document detail are invalidated together.
 */
function useVersionMutationInvalidation() {
    const queryClient = useQueryClient()

    return (documentId: string) => {
        queryClient.invalidateQueries({ queryKey: ProjectDocumentVersionQueryKey.documentVersions(documentId) })
        // `all` is a prefix of every document key, so the open document's detail refreshes with it.
        queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
    }
}

export function useCreateProjectDocumentVersionMutation() {
    const invalidate = useVersionMutationInvalidation()

    return useMutation({
        mutationFn: ({ documentId, data }: { documentId: string; data: ICreateProjectDocumentVersionInput }) =>
            createProjectDocumentVersionRequest(documentId, data),
        onSuccess: (_result, { documentId }) => invalidate(documentId),
    })
}

export function useUpdateProjectDocumentVersionsMutation() {
    const invalidate = useVersionMutationInvalidation()

    return useMutation({
        mutationFn: ({ documentId, data }: { documentId: string; data: IUpdateProjectDocumentVersionsInput }) =>
            updateProjectDocumentVersionsRequest(documentId, data),
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
        mutationFn: ({
            documentId,
            data,
        }: {
            documentId: string
            data: ISetProjectDocumentPrimaryVersionInput
        }) => setProjectDocumentPrimaryVersionRequest(documentId, data),
        onSuccess: (_result, { documentId }) => invalidate(documentId),
    })
}
