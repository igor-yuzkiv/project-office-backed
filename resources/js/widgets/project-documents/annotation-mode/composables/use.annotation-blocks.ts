import { nextTick, onMounted, ref } from 'vue'
import { collectDomBlocks, type DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import { ANNOTATABLE_BLOCK_SELECTOR } from '@/shared/utils/markdown-anchor.util'

export function useAnnotationBlocks(getPreviewRoot: () => HTMLElement | null) {
    const blocks = ref<DomBlock[]>([])

    async function refresh() {
        // md-editor-v3 announces new html from a pre-flush watcher, before the DOM is patched.
        await nextTick()

        const root = getPreviewRoot()

        blocks.value = root ? collectDomBlocks(root) : []
    }

    /** The element under the cursor may be nested inside the block that is actually annotatable. */
    function findBlockAt(target: EventTarget | null): DomBlock | null {
        let element = target instanceof HTMLElement ? target.closest<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR) : null

        while (element) {
            const block = blocks.value.find((candidate) => candidate.element === element)

            if (block) return block

            element = element.parentElement?.closest<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR) ?? null
        }

        return null
    }

    onMounted(refresh)

    return { blocks, refresh, findBlockAt }
}
