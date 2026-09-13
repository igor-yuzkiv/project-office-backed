import { describe, expect, it } from 'vitest'
import type { ThemedStatusColors } from '@/shared/types'
import { pickStatusColors } from '@/shared/components/status-pill/status-pill.util'
import { STATUS_COLORS } from '@/shared/components/status-pill/status-pill.config'

const colors: ThemedStatusColors = {
    light: { fg: '#111111', bg: '#eeeeee' },
    dark: { fg: '#eeeeee', bg: '#111111' },
}

describe('pickStatusColors', () => {
    it('returns the light pair for the light theme', () => {
        expect(pickStatusColors(colors, false)).toBe(colors.light)
    })

    it('returns the dark pair for the dark theme', () => {
        expect(pickStatusColors(colors, true)).toBe(colors.dark)
    })

    it('every shared status entry carries both pairs', () => {
        for (const entry of Object.values(STATUS_COLORS)) {
            expect(pickStatusColors(entry, false)).toEqual(entry.light)
            expect(pickStatusColors(entry, true)).toEqual(entry.dark)
        }
    })
})
