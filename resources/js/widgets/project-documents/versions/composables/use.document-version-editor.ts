import { computed, ref, watch, type MaybeRefOrGetter, toValue } from 'vue'
import {
    useCreateProjectDocumentVersionMutation,
    useDeleteProjectDocumentVersionMutation,
    useProjectDocumentVersionsQuery,
    useSetProjectDocumentPrimaryVersionMutation,
    useUpdateProjectDocumentVersionsMutation,
    type ICreateProjectDocumentVersionInput,
    type IProjectDocumentVersion,
} from '@/entities/project-document'

type VersionDraft = { content: string }

/**
 * An editing session over a document's versions. Edits to several versions live here until Save,
 * so a writer can move between them without losing what they typed in the one they left.
 *
 * Creating and deleting a version are not part of that buffer: both reach the server immediately,
 * because a version that exists only locally has no number and nothing to attach edits to.
 */
export function useDocumentVersionEditor(documentId: MaybeRefOrGetter<string>) {
    const { versions, isPending } = useProjectDocumentVersionsQuery(documentId)

    const { mutateAsync: createVersion, isPending: isCreating } = useCreateProjectDocumentVersionMutation()
    const { mutateAsync: saveVersions, isPending: isSavingVersions } = useUpdateProjectDocumentVersionsMutation()
    const { mutateAsync: deleteVersion, isPending: isDeleting } = useDeleteProjectDocumentVersionMutation()
    const { mutateAsync: setPrimaryVersion, isPending: isPinning } = useSetProjectDocumentPrimaryVersionMutation()

    const drafts = ref<Record<string, VersionDraft>>({})
    const openVersionId = ref<string | null>(null)

    const openVersion = computed(() => versions.value.find((version) => version.id === openVersionId.value) ?? null)
    const dirtyIds = computed(() => Object.keys(drafts.value))
    const isDirty = computed(() => dirtyIds.value.length > 0)

    const openContent = computed({
        get: () => {
            const version = openVersion.value

            if (!version) return ''

            return drafts.value[version.id]?.content ?? version.content ?? ''
        },
        set: (value: string) => {
            const version = openVersion.value

            if (!version) return

            // Typing back to what the server holds is not a change, so the dot goes away again.
            if (value === (version.content ?? '')) {
                delete drafts.value[version.id]

                return
            }

            drafts.value[version.id] = { content: value }
        },
    })

    watch(
        [versions, () => toValue(documentId)],
        ([current]) => {
            // A version that is gone takes its draft with it: the text can no longer be reached or
            // sent, and keeping the key would leave "Unsaved changes" lit with nothing behind it.
            for (const id of Object.keys(drafts.value)) {
                if (!current.some((version) => version.id === id)) delete drafts.value[id]
            }

            if (current.some((version) => version.id === openVersionId.value)) return

            openVersionId.value = (current.find((version) => version.is_primary) ?? current.at(-1))?.id ?? null
        },
        { immediate: true }
    )

    function selectVersion(version: IProjectDocumentVersion) {
        openVersionId.value = version.id
    }

    async function create(input: ICreateProjectDocumentVersionInput) {
        const created = await createVersion({ documentId: toValue(documentId), data: input })

        openVersionId.value = created.data.id
    }

    async function remove(version: IProjectDocumentVersion) {
        await deleteVersion({ documentId: toValue(documentId), versionId: version.id })

        // Whatever was typed into it has nowhere to go now.
        delete drafts.value[version.id]

        if (openVersionId.value === version.id) openVersionId.value = null
    }

    async function setPrimary(versionId: string | null) {
        await setPrimaryVersion({ documentId: toValue(documentId), data: { version_id: versionId } })
    }

    async function saveDrafts() {
        if (!isDirty.value) return

        const sent = versions.value
            .filter((version) => version.id in drafts.value)
            .map((version) => ({
                id: version.id,
                content: drafts.value[version.id].content,
                label: version.label,
            }))

        await saveVersions({ documentId: toValue(documentId), data: { versions: sent } })

        // Only what was actually sent stops being dirty. Typing continues during the round trip,
        // and clearing the buffer wholesale would erase text that was never in the request.
        for (const version of sent) {
            if (drafts.value[version.id]?.content === version.content) delete drafts.value[version.id]
        }
    }

    return {
        versions,
        isPending,
        openVersionId,
        openVersion,
        openContent,
        dirtyIds,
        isDirty,
        isBusy: computed(() => isCreating.value || isSavingVersions.value || isDeleting.value || isPinning.value),
        selectVersion,
        create,
        remove,
        setPrimary,
        saveDrafts,
    }
}
