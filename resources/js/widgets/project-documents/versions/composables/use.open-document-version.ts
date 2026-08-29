import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { useProjectDocumentVersionsQuery, type IProjectDocumentVersion } from '@/entities/project-document'

/**
 * Which version of a document is on screen. The list carries every version's content, so switching
 * is a local choice and never another request.
 */
export function useOpenDocumentVersion(documentId: MaybeRefOrGetter<string>) {
    const { versions, isPending } = useProjectDocumentVersionsQuery(documentId)

    const openVersionId = ref<string | null>(null)

    const openVersion = computed(() => versions.value.find((version) => version.id === openVersionId.value) ?? null)

    // A reader who opens a document gets the version the document itself would give them, and a
    // reader who switches documents starts over rather than keeping a version id from the last one.
    watch(
        [versions, () => toValue(documentId)],
        ([current]) => {
            if (current.some((version) => version.id === openVersionId.value)) return

            openVersionId.value = (current.find((version) => version.is_primary) ?? current.at(-1))?.id ?? null
        },
        { immediate: true }
    )

    function selectVersion(version: IProjectDocumentVersion) {
        openVersionId.value = version.id
    }

    return { versions, isPending, openVersionId, openVersion, selectVersion }
}
