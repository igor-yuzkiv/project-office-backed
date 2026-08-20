import { nextTick, onMounted, onScopeDispose, shallowRef } from 'vue'
import { collectDomBlocks, type DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import { ANNOTATABLE_BLOCK_SELECTOR } from '@/shared/utils/markdown-anchor.util'

export function useAnnotationBlocks(getPreviewRoot: () => HTMLElement | null) {
    // shallowRef, not ref: a deep ref would hand back reactive proxies of the elements, and
    // every identity comparison against a live DOM node would fail.
    const blocks = shallowRef<DomBlock[]>([])

    let observer: MutationObserver | undefined

    function collect() {
        const root = getPreviewRoot()

        blocks.value = root ? collectDomBlocks(root) : []
    }

    async function refresh() {
        // md-editor-v3 announces new html from a pre-flush watcher, before the DOM is patched.
        await nextTick()
        collect()
        observe()
    }

    /**
     * The preview keeps replacing its own nodes after the html event — syntax highlighting and
     * formulas land later — and every replacement detaches the elements collected so far.
     */
    function observe() {
        const root = getPreviewRoot()

        if (!root || observer) return

        observer = new MutationObserver(() => collect())
        observer.observe(root, { childList: true, subtree: true })
    }

    /** The element under the cursor may be nested inside the block that is actually annotatable. */
    function findBlockAt(target: EventTarget | null): DomBlock | null {
        if (blocks.value.some((block) => !block.element.isConnected)) collect()

        let element = target instanceof HTMLElement ? target.closest<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR) : null

        while (element) {
            const block = blocks.value.find((candidate) => candidate.element === element)

            if (block) return block

            element = element.parentElement?.closest<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR) ?? null
        }

        return null
    }

    onMounted(refresh)

    onScopeDispose(() => {
        observer?.disconnect()
        observer = undefined
    })

    return { blocks, refresh, findBlockAt }
}
