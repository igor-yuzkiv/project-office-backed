import { computed, ref, shallowRef, watch, type MaybeRefOrGetter, toValue } from 'vue'
import { onKeyStroke } from '@vueuse/core'
import type { IAnnotation } from '@/entities/annotation'
import { useProjectDocumentAnnotationsQuery } from '@/entities/project-document'
import { useToast } from '@/shared/composables/use.toast'
import type { DomBlock, DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'
import { buildAnchor, buildTextSnapshot } from '@/shared/utils/markdown-anchor.util'
import { useAnnotationAnchors } from './use.annotation-anchors'
import { useAnnotationEditor } from './use.annotation-editor'

/**
 * Annotating a document: which block is picked, what is being written about it, and which
 * annotation is being edited or re-anchored. The blocks come from whoever renders the
 * document — this composable never touches the DOM it points at.
 */
export function useAnnotationSession(
    documentId: MaybeRefOrGetter<string>,
    blocks: MaybeRefOrGetter<DomBlocks>,
    options: { enabled?: MaybeRefOrGetter<boolean> } = {}
) {
    const toast = useToast()

    // shallowRef: this holds a live DOM node, and a deep ref would wrap it in a reactive proxy.
    const selectedBlock = shallowRef<DomBlock | null>(null)

    const draft = ref('')
    const editing = ref<IAnnotation | null>(null)
    const reanchoring = ref<IAnnotation | null>(null)

    const { annotations, isPending, isError, refetch } = useProjectDocumentAnnotationsQuery(() => toValue(documentId), {
        enabled: () => (options.enabled === undefined ? true : toValue(options.enabled)),
    })
    const { orderedAnchors } = useAnnotationAnchors(annotations, blocks)
    const { create, update, remove, isSaving, isUpdating } = useAnnotationEditor(documentId)

    const isBusy = computed(() => isSaving.value || isUpdating.value)
    const isReanchoring = computed(() => reanchoring.value !== null)
    const isComposing = computed(() => selectedBlock.value !== null)

    function clearDraft() {
        draft.value = ''
        editing.value = null
    }

    async function pickBlock(block: DomBlock | null, event: MouseEvent) {
        const annotation = reanchoring.value

        if (!annotation) {
            if (block) {
                clearDraft()
                selectedBlock.value = block
            }

            return
        }

        // A block may contain a link, and picking it must not navigate away mid-request.
        event.preventDefault()

        if (!block || isUpdating.value) return

        const saved = await update(annotation.id, {
            content: annotation.content,
            text_snapshot: buildTextSnapshot(block.descriptor.text),
            anchor: buildAnchor(block.descriptor),
        })

        if (saved) {
            reanchoring.value = null
            selectedBlock.value = block
        }
    }

    async function save() {
        if (!draft.value.trim() || isBusy.value) return

        const target = editing.value

        if (target) {
            const saved = await update(target.id, {
                content: draft.value,
                text_snapshot: target.text_snapshot,
                anchor: target.anchor,
            })

            if (saved) clearDraft()

            return
        }

        const block = selectedBlock.value

        if (!block) return

        const saved = await create({
            content: draft.value,
            text_snapshot: buildTextSnapshot(block.descriptor.text),
            anchor: buildAnchor(block.descriptor),
        })

        if (saved) clearDraft()
    }

    function cancel() {
        clearDraft()
        selectedBlock.value = null
    }

    /** Returns the block an annotation currently sits on, after scrolling it into view. */
    function selectAnnotation(annotation: IAnnotation): DomBlock | null {
        const block = orderedAnchors.value.find((item) => item.annotation.id === annotation.id)?.block ?? null

        clearDraft()
        selectedBlock.value = block

        if (!block) {
            toast.info('This annotation has lost its block. Use Re-anchor to attach it again.')

            return null
        }

        block.element.scrollIntoView({ behavior: 'smooth', block: 'center' })

        return block
    }

    function editAnnotation(annotation: IAnnotation) {
        if (!selectAnnotation(annotation)) return

        editing.value = annotation
        draft.value = annotation.content
    }

    function startReanchoring(annotation: IAnnotation) {
        reanchoring.value = annotation
        clearDraft()
        // The composer must go: while picking a target, Enter would write a new annotation instead.
        selectedBlock.value = null
    }

    function cancelReanchoring() {
        reanchoring.value = null
    }

    onKeyStroke('Escape', () => (isReanchoring.value ? cancelReanchoring() : cancel()))

    /**
     * Everything here points at one document's blocks, and the session can outlive them: the
     * workspace reuses the same page instance from document to document, and a document edited
     * to empty takes its rendered blocks away without ending the session. A draft carried across
     * either boundary would be saved against a block the reader is no longer looking at.
     */
    watch([() => toValue(documentId), () => (options.enabled === undefined ? true : toValue(options.enabled))], () => {
        clearDraft()
        reanchoring.value = null
        selectedBlock.value = null
    })

    return {
        selectedBlock,
        draft,
        editing,
        reanchoring,
        orderedAnchors,
        isPending,
        isError,
        isBusy,
        isReanchoring,
        isComposing,
        refetch,
        pickBlock,
        save,
        cancel,
        selectAnnotation,
        editAnnotation,
        startReanchoring,
        cancelReanchoring,
        removeAnnotation: remove,
    }
}
