import { nextTick, onMounted, shallowRef } from 'vue'
import { useMutationObserver } from '@vueuse/core'
import {
    collectDomBlocks,
    EMPTY_DOM_BLOCKS,
    type DomBlock,
    type DomBlocks,
} from '@/shared/utils/markdown-anchor.dom.util'
import { ANNOTATABLE_BLOCK_SELECTOR } from '@/shared/utils/markdown-anchor.util'

export function useAnnotationBlocks(getPreviewRoot: () => HTMLElement | null) {
    // shallowRef, not ref: a deep ref would hand back reactive proxies of the elements, and
    // every identity comparison against a live DOM node would fail.
    const blocks = shallowRef<DomBlocks>(EMPTY_DOM_BLOCKS)

    let frame = 0

    function collect() {
        const root = getPreviewRoot()
        const next = root ? collectDomBlocks(root) : EMPTY_DOM_BLOCKS

        // Highlighting rewrites the innards of code blocks without changing the block set;
        // keeping the old value then costs nothing downstream.
        const unchanged =
            next.blocks.length === blocks.value.blocks.length &&
            next.blocks.every((block, index) => {
                const current = blocks.value.blocks[index]

                return current?.element === block.element && current.descriptor.textHash === block.descriptor.textHash
            })

        if (!unchanged) blocks.value = next
    }

    /**
     * The preview keeps replacing its own nodes after the html event — syntax highlighting and
     * formulas land later — and each pass would otherwise cost a full re-collect of its own.
     */
    function scheduleCollect() {
        if (frame) return

        frame = requestAnimationFrame(() => {
            frame = 0
            collect()
        })
    }

    async function refresh() {
        // md-editor-v3 announces new html from a pre-flush watcher, before the DOM is patched.
        await nextTick()
        collect()
    }

    /** The element under the cursor may be nested inside the block that is actually annotatable. */
    function findBlockAt(target: EventTarget | null): DomBlock | null {
        let element = target instanceof HTMLElement ? target.closest<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR) : null

        while (element) {
            const block = blocks.value.byElement.get(element)

            if (block) return block

            element = element.parentElement?.closest<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR) ?? null
        }

        return null
    }

    useMutationObserver(getPreviewRoot, scheduleCollect, { childList: true, subtree: true })

    onMounted(refresh)

    return { blocks, refresh, findBlockAt }
}
