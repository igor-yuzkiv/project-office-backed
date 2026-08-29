import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQueryClient } from '@tanstack/vue-query'
import {
    ProjectDocumentVersionQueryKey,
    useCreateProjectDocumentVersionMutation,
    useDeleteProjectDocumentVersionMutation,
    useSetProjectDocumentPrimaryVersionMutation,
    useUpdateProjectDocumentVersionMutation,
    type ICreateProjectDocumentVersionInput,
    type IUpdateProjectDocumentVersionInput,
    type IProjectDocumentVersion,
} from '@/entities/project-document'

/**
 * The writes a document's version list can ask for. No buffer of its own: content is written
 * elsewhere, one version at a time, and these reach the server as soon as they are called.
 */
export function useDocumentVersionActions(documentId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    const { mutateAsync: createVersion, isPending: isCreating } = useCreateProjectDocumentVersionMutation()
    const { mutateAsync: updateVersion, isPending: isUpdating } = useUpdateProjectDocumentVersionMutation()
    const { mutateAsync: deleteVersion, isPending: isDeleting } = useDeleteProjectDocumentVersionMutation()
    const { mutateAsync: setPrimaryVersion, isPending: isPinning } = useSetProjectDocumentPrimaryVersionMutation()

    /** Resolves once the list holds the new version, so it can be opened without a gap. */
    async function create(input: ICreateProjectDocumentVersionInput): Promise<IProjectDocumentVersion> {
        const created = await createVersion({ documentId: toValue(documentId), data: input })

        await queryClient.invalidateQueries({
            queryKey: ProjectDocumentVersionQueryKey.documentVersions(toValue(documentId)),
        })

        return created.data
    }

    async function update(version: IProjectDocumentVersion, input: IUpdateProjectDocumentVersionInput) {
        await updateVersion({ documentId: toValue(documentId), versionId: version.id, data: input })
    }

    async function remove(version: IProjectDocumentVersion) {
        await deleteVersion({ documentId: toValue(documentId), versionId: version.id })
    }

    /** Null returns the document to following its newest version. */
    async function setPrimary(versionId: string | null) {
        await setPrimaryVersion({ documentId: toValue(documentId), data: { version_id: versionId } })
    }

    return {
        create,
        update,
        remove,
        setPrimary,
        isBusy: computed(() => isCreating.value || isUpdating.value || isDeleting.value || isPinning.value),
    }
}
