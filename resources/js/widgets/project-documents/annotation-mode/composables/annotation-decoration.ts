/**
 * The markdown arrives through v-html, so scoped styles never reach it and the classes below
 * are put on the nodes by hand. They live together with the rules in DocumentAnnotationMode.
 */
export const ANNOTATION_CLASS = {
    anchored: 'annotation-anchored',
    hovered: 'annotation-hovered',
    selected: 'annotation-selected',
} as const

/** Moves a class from one element to another, touching neither when nothing changed. */
export function moveClass(className: string, from: HTMLElement | null, to: HTMLElement | null) {
    if (from === to) return

    from?.classList.remove(className)
    to?.classList.add(className)
}

/** Reconciles a class across a set of elements, writing only to the difference. */
export function syncClass(className: string, previous: HTMLElement[], next: HTMLElement[]): HTMLElement[] {
    const wanted = new Set(next)

    previous.filter((element) => !wanted.has(element)).forEach((element) => element.classList.remove(className))
    next.filter((element) => !previous.includes(element)).forEach((element) => element.classList.add(className))

    return next
}
