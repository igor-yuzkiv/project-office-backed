export const ANNOTATABLE_BLOCK_SELECTOR = 'p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, table'

const SNAPSHOT_LENGTH = 300

/** Below this Dice coefficient a block that kept its position is treated as a different block. */
const POSITION_SIMILARITY_THRESHOLD = 0.5

export interface BlockDescriptor {
    tag: string
    line: number | null
    ordinal: number
    index: number
    textHash: string
    text: string
}

export interface BlockAnchor {
    version: 1
    line: number | null
    tag: string
    ordinal: number
    index: number
    text_hash: string
}

export type AnchorMatchKind = 'exact' | 'hash' | 'position'

export interface AnchorMatch {
    descriptor: BlockDescriptor
    kind: AnchorMatchKind
}

export function normalizeBlockText(text: string): string {
    return text.normalize('NFC').replace(/\s+/g, ' ').trim()
}

/** FNV-1a 32-bit — synchronous, dependency-free, and identical in Node and the browser. */
export function hashBlockText(text: string): string {
    const normalized = normalizeBlockText(text)
    let hash = 0x811c9dc5

    for (let i = 0; i < normalized.length; i++) {
        hash ^= normalized.charCodeAt(i)
        hash = Math.imul(hash, 0x01000193)
    }

    return (hash >>> 0).toString(16).padStart(8, '0')
}

function bigrams(text: string): string[] {
    const pairs: string[] = []

    for (let i = 0; i < text.length - 1; i++) {
        pairs.push(text.slice(i, i + 2))
    }

    return pairs
}

/** Dice coefficient over bigrams of the normalized strings. */
export function textSimilarity(a: string, b: string): number {
    const left = normalizeBlockText(a)
    const right = normalizeBlockText(b)

    if (left === right) return 1
    if (!left || !right) return 0

    const leftPairs = bigrams(left)
    const rightPairs = bigrams(right)

    if (!leftPairs.length || !rightPairs.length) return 0

    // Counted rather than spliced out of a copy: this runs for every annotation that
    // reaches the similarity check, which is exactly when a document was just edited.
    const remaining = new Map<string, number>()

    for (const pair of rightPairs) {
        remaining.set(pair, (remaining.get(pair) ?? 0) + 1)
    }

    let shared = 0

    for (const pair of leftPairs) {
        const left = remaining.get(pair)

        if (left) {
            remaining.set(pair, left - 1)
            shared++
        }
    }

    return (2 * shared) / (leftPairs.length + rightPairs.length)
}

export function buildBlockDescriptors(
    blocks: Array<{ tag: string; line: number | null; text: string }>
): BlockDescriptor[] {
    const ordinals = new Map<string, number>()

    return blocks.map((block, index) => {
        const key = `${block.line}:${block.tag}`
        const ordinal = ordinals.get(key) ?? 0

        ordinals.set(key, ordinal + 1)

        return {
            tag: block.tag,
            line: block.line,
            ordinal,
            index,
            textHash: hashBlockText(block.text),
            text: block.text,
        }
    })
}

export function buildAnchor(descriptor: BlockDescriptor): BlockAnchor {
    return {
        version: 1,
        line: descriptor.line,
        tag: descriptor.tag,
        ordinal: descriptor.ordinal,
        index: descriptor.index,
        text_hash: descriptor.textHash,
    }
}

export function buildTextSnapshot(text: string): string {
    const normalized = normalizeBlockText(text)

    if (normalized.length <= SNAPSHOT_LENGTH) return normalized

    // Slicing by code unit can cut an emoji in half, and a lone surrogate is not valid JSON.
    const truncated = normalized.slice(0, SNAPSHOT_LENGTH).replace(/[\uD800-\uDBFF]$/, '')

    return `${truncated}…`
}

function findExact(anchor: BlockAnchor, descriptors: BlockDescriptor[]): BlockDescriptor | undefined {
    return descriptors.find(
        (descriptor) =>
            descriptor.tag === anchor.tag &&
            descriptor.line === anchor.line &&
            descriptor.ordinal === anchor.ordinal &&
            descriptor.textHash === anchor.text_hash
    )
}

function findByHash(anchor: BlockAnchor, descriptors: BlockDescriptor[]): BlockDescriptor | undefined {
    const candidates = descriptors.filter(
        (descriptor) => descriptor.tag === anchor.tag && descriptor.textHash === anchor.text_hash
    )

    return candidates.reduce<BlockDescriptor | undefined>((closest, candidate) => {
        if (!closest) return candidate

        const candidateDistance = Math.abs(candidate.index - anchor.index)
        const closestDistance = Math.abs(closest.index - anchor.index)

        if (candidateDistance < closestDistance) return candidate
        if (candidateDistance > closestDistance) return closest

        return candidate.index < closest.index ? candidate : closest
    }, undefined)
}

function findByPosition(
    anchor: BlockAnchor,
    textSnapshot: string | null,
    descriptors: BlockDescriptor[]
): BlockDescriptor | undefined {
    if (anchor.line === null) return undefined

    const candidate = descriptors.find(
        (descriptor) =>
            descriptor.tag === anchor.tag && descriptor.line === anchor.line && descriptor.ordinal === anchor.ordinal
    )

    if (!candidate) return undefined

    // Without this the block that took the deleted one's place would silently inherit its annotation.
    // Both sides are truncated the same way, so the threshold means the same thing at any block length.
    return textSimilarity(textSnapshot ?? '', buildTextSnapshot(candidate.text)) >= POSITION_SIMILARITY_THRESHOLD
        ? candidate
        : undefined
}

export function resolveAnchor(
    anchor: BlockAnchor,
    textSnapshot: string | null,
    descriptors: BlockDescriptor[]
): AnchorMatch | null {
    const exact = findExact(anchor, descriptors)

    if (exact) return { descriptor: exact, kind: 'exact' }

    // An empty snapshot means every empty block would match every other one.
    if (normalizeBlockText(textSnapshot ?? '')) {
        const byHash = findByHash(anchor, descriptors)

        if (byHash) return { descriptor: byHash, kind: 'hash' }
    }

    const byPosition = findByPosition(anchor, textSnapshot, descriptors)

    return byPosition ? { descriptor: byPosition, kind: 'position' } : null
}
