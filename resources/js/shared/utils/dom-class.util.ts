/**
 * Markdown arrives through v-html, so scoped styles never reach it and decoration classes are
 * put on the nodes by hand. These helpers write only the difference, leaving untouched nodes
 * alone — the preview replaces its own elements often, and a blanket rewrite would fight it.
 */

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
