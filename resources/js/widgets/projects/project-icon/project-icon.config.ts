import type { ComponentSize } from '@/shared/types'

export const PROJECT_ICON_SIZE_MAP: Record<ComponentSize, { root: string; label: string; glyph: string }> = {
    // The plate is square for an icon and grows sideways for a prefix, which runs up to five letters.
    xsmall: { root: 'h-6 min-w-6 px-1', label: 'text-[9px]', glyph: 'text-xs' },
    small: { root: 'h-7 min-w-7 px-1', label: 'text-[10px]', glyph: 'text-sm' },
    medium: { root: 'h-9 min-w-9 px-1.5', label: 'text-xs', glyph: 'text-lg' },
    large: { root: 'h-11 min-w-11 px-2', label: 'text-sm', glyph: 'text-xl' },
    xlarge: { root: 'h-13 min-w-13 px-2', label: 'text-base', glyph: 'text-2xl' },
}

/**
 * A plate is tinted by the project's prefix, not by its status: the tint is there to tell one
 * project from another at a glance, and it must not move when a project changes status.
 */
export const PROJECT_ICON_TINTS = [
    'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200',
    'bg-violet-100 text-violet-800 dark:bg-violet-900/60 dark:text-violet-200',
    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200',
    'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200',
    'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200',
    'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/60 dark:text-cyan-200',
    'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200',
    'bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-200',
] as const

/** Same prefix, same tint, on every screen and every reload. */
export function projectTintClass(prefix: string): string {
    let hash = 0

    for (const character of prefix) {
        hash = (hash * 31 + character.codePointAt(0)!) % 100_000
    }

    return PROJECT_ICON_TINTS[hash % PROJECT_ICON_TINTS.length]
}
