import { computed, onScopeDispose, ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { useQueryClient } from '@tanstack/vue-query'
import {
    ProjectDocumentVersionQueryKey,
    updateProjectDocumentVersionContentRequest,
    type IProjectDocumentVersionsResponse,
} from '@/entities/project-document'
import { useLoadingStateStore } from '@/app/stores/use.loading-state.store'

type AutosaveStatus = 'idle' | 'saving' | 'saved' | 'error'

const AUTOSAVE_DELAY_MS = 1500

// A draft remembers the document it was typed into: the page is reused across documents, so by
// the time a version is written its owner may no longer be the open document.
interface Draft {
    documentId: string
    content: string
}

/**
 * The text being edited, kept apart from the text the server has. `value` reads the draft when
 * there is one and the server's content otherwise, so a background refetch never overwrites what
 * is being typed. Drafts are kept per version: a version whose save failed keeps its text until it
 * is opened again and saved, or until `flush` writes everything that is pending.
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

    const drafts = shallowRef(new Map<string, Draft>())
    // The toolbar's status is about the open version only; writes of other versions stay quiet.
    const status = ref<AutosaveStatus>('idle')
    const lastSavedAt = ref<Date | null>(null)

    let timer: ReturnType<typeof setTimeout> | null = null
    const writes = new Map<string, Promise<boolean>>()
    let writesInFlight = 0

    const draft = computed(() => {
        const id = toValue(options.versionId)

        return id === null ? null : (drafts.value.get(id)?.content ?? null)
    })

    const isDirty = computed(() => drafts.value.size > 0)

    const value = computed<string>({
        get: () => draft.value ?? toValue(options.serverContent) ?? '',
        set: (next) => {
            const id = toValue(options.versionId)

            if (id === null) return

            setDraft(
                id,
                next === (toValue(options.serverContent) ?? '')
                    ? null
                    : { documentId: toValue(options.documentId), content: next }
            )
            schedule()
        },
    })

    function setDraft(versionId: string, content: Draft | null) {
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

    function isOpen(versionId: string) {
        return versionId === toValue(options.versionId)
    }

    async function push(versionId: string, draft: Draft): Promise<boolean> {
        if (isOpen(versionId)) status.value = 'saving'
        writesInFlight += 1
        loadingStore.progressLoading = true

        try {
            const saved = await updateProjectDocumentVersionContentRequest(draft.documentId, versionId, {
                content: draft.content,
            })

            queryClient.setQueryData<IProjectDocumentVersionsResponse>(
                ProjectDocumentVersionQueryKey.documentVersions(draft.documentId),
                (current) =>
                    current && { data: current.data.map((item) => (item.id === saved.data.id ? saved.data : item)) }
            )

            // Typing continued during the round trip: the draft is only settled if it still says
            // what was sent.
            if (drafts.value.get(versionId)?.content === draft.content) setDraft(versionId, null)

            if (isOpen(versionId)) {
                status.value = 'saved'
                lastSavedAt.value = new Date()
            }

            return true
        } catch {
            if (isOpen(versionId)) status.value = 'error'

            return false
        } finally {
            writesInFlight -= 1
            if (writesInFlight === 0) loadingStore.progressLoading = false
        }
    }

    /**
     * Writes one version's draft, again if it changed while the request was out; true when there
     * was nothing left to write. One write per version at a time — a second call joins the first.
     */
    function saveVersion(versionId: string): Promise<boolean> {
        const pending = writes.get(versionId)

        if (pending) return pending

        const run = (async () => {
            for (let draft = drafts.value.get(versionId); draft; draft = drafts.value.get(versionId)) {
                if (!(await push(versionId, draft))) return false
            }

            return true
        })().finally(() => writes.delete(versionId))

        writes.set(versionId, run)

        return run
    }

    function save(): Promise<boolean> {
        const id = toValue(options.versionId)

        return id === null ? Promise.resolve(true) : saveVersion(id)
    }

    /** Writes every pending draft now instead of after the pause; true only when all of them landed. */
    async function flush(): Promise<boolean> {
        clearTimer()

        const results = await Promise.all([...drafts.value.keys()].map(saveVersion))

        return results.every(Boolean)
    }

    /** Drops the open version's draft without saving — for a version that no longer exists to be saved to. */
    function cancel() {
        clearTimer()

        const id = toValue(options.versionId)

        if (id !== null) setDraft(id, null)
    }

    // Leaving a version writes it first; its draft survives a failed write and is retried when
    // the version is opened again or everything is flushed. The status starts over with the version.
    watch(
        () => toValue(options.versionId),
        (_next, previous) => {
            clearTimer()
            status.value = 'idle'
            lastSavedAt.value = null

            if (previous && drafts.value.has(previous)) void saveVersion(previous)
        }
    )

    onScopeDispose(clearTimer)

    return { value, status, lastSavedAt, isDirty, flush, cancel }
}
