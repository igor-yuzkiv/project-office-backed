import {
    ANNOTATABLE_BLOCK_SELECTOR,
    buildBlockDescriptors,
    resolveAnchor,
    type AnchorMatchKind,
    type BlockAnchor,
    type BlockDescriptor,
} from '@/shared/utils/markdown-anchor.util'

export interface DomBlock {
    element: HTMLElement
    descriptor: BlockDescriptor
}

export interface DomBlocks {
    blocks: DomBlock[]
    descriptors: BlockDescriptor[]
    /** Lookup for the element under the cursor, so hovering does not scan the document. */
    byElement: Map<HTMLElement, DomBlock>
}

export interface DomBlockMatch {
    block: DomBlock
    kind: AnchorMatchKind
}

export const EMPTY_DOM_BLOCKS: DomBlocks = { blocks: [], descriptors: [], byElement: new Map() }

/** A block with its own text is annotatable even when it also contains nested candidates. */
function hasOwnText(element: HTMLElement): boolean {
    return Array.from(element.childNodes).some(
        (node) => node.nodeType === Node.TEXT_NODE && (node.textContent?.trim() ?? '') !== ''
    )
}

function readLine(element: HTMLElement, previewRoot: HTMLElement): number | null {
    let current: HTMLElement | null = element

    while (current) {
        const line = current.dataset.line

        if (line !== undefined) return Number(line)
        if (current === previewRoot) break

        current = current.parentElement
    }

    return null
}

export function collectDomBlocks(previewRoot: HTMLElement): DomBlocks {
    const elements = Array.from(previewRoot.querySelectorAll<HTMLElement>(ANNOTATABLE_BLOCK_SELECTOR)).filter(
        (element) => !element.querySelector(ANNOTATABLE_BLOCK_SELECTOR) || hasOwnText(element)
    )

    const descriptors = buildBlockDescriptors(
        elements.map((element) => ({
            tag: element.tagName.toLowerCase(),
            line: readLine(element, previewRoot),
            text: element.textContent ?? '',
        }))
    )

    const blocks = elements.map((element, index) => ({ element, descriptor: descriptors[index]! }))

    return { blocks, descriptors, byElement: new Map(blocks.map((block) => [block.element, block])) }
}

export function findBlock(anchor: BlockAnchor, textSnapshot: string | null, blocks: DomBlocks): DomBlockMatch | null {
    const match = resolveAnchor(anchor, textSnapshot, blocks.descriptors)

    // A descriptor's index is its position in the very array the blocks were built from.
    const block = match ? blocks.blocks[match.descriptor.index] : undefined

    return block && match ? { block, kind: match.kind } : null
}
