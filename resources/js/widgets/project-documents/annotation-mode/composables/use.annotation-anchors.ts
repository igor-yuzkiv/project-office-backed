import { computed, onScopeDispose, type MaybeRefOrGetter, toValue, watch } from 'vue'
import type { IAnnotation } from '@/entities/annotation'
import { findBlockElement, type DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import type { AnchorMatchKind } from '@/shared/utils/markdown-anchor.util'

const ANCHORED_CLASS = 'annotation-anchored'

function byCreation(left: AnnotationAnchor, right: AnnotationAnchor): number {
    return left.annotation.created_at.localeCompare(right.annotation.created_at)
}

export interface AnnotationAnchor {
    annotation: IAnnotation
    element: HTMLElement | null
    kind: AnchorMatchKind | null
    /** Position of the resolved block in the document; null for an orphaned annotation. */
    index: number | null
}

export function useAnnotationAnchors(
    annotations: MaybeRefOrGetter<IAnnotation[]>,
    blocks: MaybeRefOrGetter<DomBlock[]>
) {
    const anchors = computed<AnnotationAnchor[]>(() =>
        toValue(annotations).map((annotation) => {
            const match = findBlockElement(annotation.anchor, annotation.text_snapshot, toValue(blocks))

            return {
                annotation,
                element: match?.element ?? null,
                kind: match?.kind ?? null,
                index: match?.descriptor.index ?? null,
            }
        })
    )

    /** Document order, then creation order within one block; orphaned annotations come last. */
    const orderedAnchors = computed<AnnotationAnchor[]>(() =>
        [...anchors.value].sort((left, right) => {
            if (left.index === null && right.index === null) return byCreation(left, right)
            if (left.index === null) return 1
            if (right.index === null) return -1

            return left.index === right.index ? byCreation(left, right) : left.index - right.index
        })
    )

    const orphaned = computed(() => anchors.value.filter((anchor) => anchor.element === null))

    function annotationsOf(element: HTMLElement): IAnnotation[] {
        return anchors.value.filter((anchor) => anchor.element === element).map((anchor) => anchor.annotation)
    }

    // The markdown comes from v-html, so scoped styles cannot reach it — the class is put on by hand.
    let decorated: HTMLElement[] = []

    function undecorate() {
        decorated.forEach((element) => element.classList.remove(ANCHORED_CLASS))
        decorated = []
    }

    watch(
        anchors,
        (current) => {
            undecorate()
            decorated = [...new Set(current.map((anchor) => anchor.element).filter((el) => el !== null))]
            decorated.forEach((element) => element.classList.add(ANCHORED_CLASS))
        },
        { immediate: true }
    )

    onScopeDispose(undecorate)

    return { anchors, orderedAnchors, orphaned, annotationsOf }
}
