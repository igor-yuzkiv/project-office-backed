import type { StatusColors, ThemedStatusColors } from '@/shared/types'

export function pickStatusColors(colors: ThemedStatusColors, isDark: boolean): StatusColors {
    return isDark ? colors.dark : colors.light
}
