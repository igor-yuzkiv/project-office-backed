import type { ComponentSize } from '@/shared/types'

export const PROJECT_ICON_SIZE_MAP: Record<ComponentSize, { root: string; label: string; glyph: string }> = {
    xsmall: { root: 'h-6 w-6', label: 'text-[9px]', glyph: 'text-xs' },
    small: { root: 'h-7 w-7', label: 'text-[10px]', glyph: 'text-sm' },
    medium: { root: 'h-9 w-9', label: 'text-xs', glyph: 'text-lg' },
    large: { root: 'h-11 w-11', label: 'text-sm', glyph: 'text-xl' },
    xlarge: { root: 'h-13 w-13', label: 'text-base', glyph: 'text-2xl' },
}

/**
 * A plate is tinted by the project's prefix, not by its status: the tint is there to tell one
 * project from another at a glance, and it must not move when a project changes status.
 */
export const PROJECT_ICON_TINTS = [
    'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-300',
    'bg-violet-50 text-violet-600 dark:bg-violet-950/60 dark:text-violet-300',
    'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-300',
    'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300',
    'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300',
    'bg-cyan-50 text-cyan-600 dark:bg-cyan-950/60 dark:text-cyan-300',
    'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300',
    'bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-300',
] as const

/** Same prefix, same tint, on every screen and every reload. */
export function projectTintClass(prefix: string): string {
    let hash = 0

    for (const character of prefix) {
        hash = (hash * 31 + character.codePointAt(0)!) % 100_000
    }

    return PROJECT_ICON_TINTS[hash % PROJECT_ICON_TINTS.length]
}
