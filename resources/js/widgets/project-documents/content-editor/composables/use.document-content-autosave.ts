import { computed, onScopeDispose, ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { useQueryClient } from '@tanstack/vue-query'
import {
    ProjectDocumentVersionQueryKey,
    updateProjectDocumentVersionContentRequest,
    type IProjectDocumentVersionsResponse,
} from '@/entities/project-document'
import { useLoadingStateStore } from '@/app/stores/use.loading-state.store'

export type AutosaveStatus = 'idle' | 'saving' | 'saved' | 'error'

const AUTOSAVE_DELAY_MS = 1500

/**
 * The text being edited, kept apart from the text the server has. `value` reads the draft when
 * there is one and the server's content otherwise, so a background refetch never overwrites what
 * is being typed. Drafts are kept per version: a version whose save failed keeps its text until it
 * is opened again and saved.
 *
 * A successful save patches the version in the versions cache rather than invalidating it — the
 * list carries every version's full content, and refetching all of it on each keystroke pause is
 * the cost invalidation would have.
 */
export function useDocumentContentAutosave(options: {
    documentId: MaybeRefOrGetter<string>
    versionId: MaybeRefOrGetter<string | null>
    serverContent: MaybeRefOrGetter<string | null>
}) {
    const queryClient = useQueryClient()
    const loadingStore = useLoadingStateStore()

    const drafts = shallowRef(new Map<string, string>())
    const status = ref<AutosaveStatus>('idle')
    const lastSavedAt = ref<Date | null>(null)

    let timer: ReturnType<typeof setTimeout> | null = null
    let inFlight: Promise<boolean> | null = null

    const draft = computed(() => {
        const id = toValue(options.versionId)

        return id === null ? null : (drafts.value.get(id) ?? null)
    })

    const isDirty = computed(() => draft.value !== null)

    const value = computed<string>({
        get: () => draft.value ?? toValue(options.serverContent) ?? '',
        set: (next) => {
            const id = toValue(options.versionId)

            if (id === null) return

            setDraft(id, next === (toValue(options.serverContent) ?? '') ? null : next)
            schedule()
        },
    })

    function setDraft(versionId: string, content: string | null) {
        const next = new Map(drafts.value)

        if (content === null) next.delete(versionId)
        else next.set(versionId, content)

        drafts.value = next
    }

    function clearTimer() {
        if (timer !== null) clearTimeout(timer)
        timer = null
    }

    function schedule() {
        clearTimer()
        timer = setTimeout(() => void save(), AUTOSAVE_DELAY_MS)
    }

    /** Writes one version's draft; true when there was nothing to write or the write landed. */
    async function saveVersion(versionId: string): Promise<boolean> {
        const content = drafts.value.get(versionId)

        if (content === undefined) return true

        status.value = 'saving'
        loadingStore.progressLoading = true

        try {
            const saved = await updateProjectDocumentVersionContentRequest(toValue(options.documentId), versionId, {
                content,
            })

            queryClient.setQueryData<IProjectDocumentVersionsResponse>(
                ProjectDocumentVersionQueryKey.documentVersions(toValue(options.documentId)),
                (current) =>
                    current && { data: current.data.map((item) => (item.id === saved.data.id ? saved.data : item)) }
            )

            // Typing continued during the round trip: the draft is only settled if it still says
            // what was sent.
            if (drafts.value.get(versionId) === content) setDraft(versionId, null)

            status.value = 'saved'
            lastSavedAt.value = new Date()

            return true
        } catch {
            status.value = 'error'

            return false
        } finally {
            loadingStore.progressLoading = false
        }
    }

    function save(): Promise<boolean> {
        const id = toValue(options.versionId)

        if (id === null) return Promise.resolve(true)
        if (inFlight) return inFlight

        inFlight = saveVersion(id).finally(() => {
            inFlight = null
        })

        return inFlight
    }

    /** Saves now instead of after the pause. */
    function flush(): Promise<boolean> {
        clearTimer()

        return save()
    }

    /** Drops the draft without saving — for a version that no longer exists to be saved to. */
    function cancel() {
        clearTimer()

        const id = toValue(options.versionId)

        if (id !== null) setDraft(id, null)
    }

    // Leaving a version writes it first; its draft survives a failed write and is retried when
    // the version is opened again.
    watch(
        () => toValue(options.versionId),
        (_next, previous) => {
            clearTimer()

            if (previous && drafts.value.has(previous)) void saveVersion(previous)
        }
    )

    onScopeDispose(clearTimer)

    return { value, status, lastSavedAt, isDirty, save, flush, cancel }
}
