import { computed, onScopeDispose, type MaybeRefOrGetter, toValue, watch } from 'vue'
import type { IAnnotation } from '@/entities/annotation'
import { findBlock, type DomBlock, type DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'
import type { AnchorMatchKind } from '@/shared/utils/markdown-anchor.util'
import { ANNOTATION_CLASS, syncClass } from './annotation-decoration'

export interface AnnotationAnchor {
    annotation: IAnnotation
    /** The block the anchor resolved to; null when the annotation is orphaned. */
    block: DomBlock | null
    kind: AnchorMatchKind | null
}

function byCreation(left: AnnotationAnchor, right: AnnotationAnchor): number {
    return left.annotation.created_at.localeCompare(right.annotation.created_at)
}

function position(anchor: AnnotationAnchor): number | null {
    return anchor.block?.descriptor.index ?? null
}

export function useAnnotationAnchors(
    annotations: MaybeRefOrGetter<IAnnotation[]>,
    blocks: MaybeRefOrGetter<DomBlocks>
) {
    /** Document order, then creation order within one block; orphaned annotations come last. */
    const orderedAnchors = computed<AnnotationAnchor[]>(() => {
        const resolved = toValue(annotations).map<AnnotationAnchor>((annotation) => {
            const match = findBlock(annotation.anchor, annotation.text_snapshot, toValue(blocks))

            return { annotation, block: match?.block ?? null, kind: match?.kind ?? null }
        })

        return resolved.sort((left, right) => {
            const leftIndex = position(left)
            const rightIndex = position(right)

            if (leftIndex === null && rightIndex === null) return byCreation(left, right)
            if (leftIndex === null) return 1
            if (rightIndex === null) return -1

            return leftIndex === rightIndex ? byCreation(left, right) : leftIndex - rightIndex
        })
    })

    let decorated: HTMLElement[] = []

    watch(
        orderedAnchors,
        (current) => {
            const elements = [...new Set(current.map((anchor) => anchor.block?.element).filter((el) => el != null))]

            decorated = syncClass(ANNOTATION_CLASS.anchored, decorated, elements)
        },
        { immediate: true }
    )

    onScopeDispose(() => {
        decorated = syncClass(ANNOTATION_CLASS.anchored, decorated, [])
    })

    return { orderedAnchors }
}
