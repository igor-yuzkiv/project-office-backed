import type { ThemedStatusColors } from '@/shared/types'

/**
 * Status pairs on the Islands palette, one entry per meaning; the pill draws its border from `fg`. Entity status
 * configs point at these instead of carrying their own hex values.
 */
export const STATUS_COLORS = {
    backlog: { light: { fg: '#4e5157', bg: '#ebecf0' }, dark: { fg: '#b4b8bf', bg: '#393b40' } },
    open: { light: { fg: '#4e5157', bg: '#ebecf0' }, dark: { fg: '#b4b8bf', bg: '#393b40' } },
    ready: { light: { fg: '#7a3ec8', bg: '#eee3fc' }, dark: { fg: '#c3a4f7', bg: '#352b4a' } },
    progress: { light: { fg: '#2462d9', bg: '#d4e2ff' }, dark: { fg: '#8ab4ff', bg: '#25324d' } },
    test: { light: { fg: '#a85c00', bg: '#fcebd3' }, dark: { fg: '#f0b060', bg: '#45331c' } },
    done: { light: { fg: '#1e7b34', bg: '#dcf2df' }, dark: { fg: '#7dd08a', bg: '#253627' } },
    closed: { light: { fg: '#3b3e44', bg: '#dfe1e5' }, dark: { fg: '#a8adb5', bg: '#2b2d30' } },
    declined: { light: { fg: '#c4302b', bg: '#fbe0df' }, dark: { fg: '#ff8f8a', bg: '#4a2524' } },
} as const satisfies Record<string, ThemedStatusColors>

/** What a pill shows when the status is unknown or missing. */
export const STATUS_COLORS_FALLBACK: ThemedStatusColors = STATUS_COLORS.open
