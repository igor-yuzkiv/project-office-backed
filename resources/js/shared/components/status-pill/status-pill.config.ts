import type { ThemedStatusColors } from '@/shared/types'

/**
 * The `--st-*` pairs of the redesign mockups (`design.css`), one entry per meaning. Entity status
 * configs point at these instead of carrying their own hex values.
 */
export const STATUS_COLORS = {
    backlog: { light: { fg: '#7c8797', bg: '#eef2f6' }, dark: { fg: '#8b97a6', bg: '#1b222b' } },
    open: { light: { fg: '#7c8797', bg: '#eef2f6' }, dark: { fg: '#8b97a6', bg: '#1b222b' } },
    ready: { light: { fg: '#6d5bd0', bg: '#efecfb' }, dark: { fg: '#a99cf5', bg: '#23244a' } },
    progress: { light: { fg: '#2f6fe0', bg: '#e6efff' }, dark: { fg: '#7fb0ff', bg: '#163464' } },
    test: { light: { fg: '#b45309', bg: '#fdf1e0' }, dark: { fg: '#e0a458', bg: '#3a2c16' } },
    done: { light: { fg: '#15803d', bg: '#e6f4ea' }, dark: { fg: '#5cc98a', bg: '#14321f' } },
    closed: { light: { fg: '#475569', bg: '#e2e8f0' }, dark: { fg: '#8b97a6', bg: '#1c232c' } },
    declined: { light: { fg: '#b91c1c', bg: '#fdecec' }, dark: { fg: '#f28b8b', bg: '#41202a' } },
} as const satisfies Record<string, ThemedStatusColors>

/** What a pill shows when the status is unknown or missing. */
export const STATUS_COLORS_FALLBACK: ThemedStatusColors = STATUS_COLORS.open
