import type { HexColor } from '@/shared/types'

export function randomHex(): HexColor {
    return ('#' +
        Math.floor(Math.random() * 0xffffff)
            .toString(16)
            .padStart(6, '0')) as HexColor
}

export function getContrastColor(hex: HexColor): string {
    const r = parseInt(hex.slice(1, 3), 16)
    const g = parseInt(hex.slice(3, 5), 16)
    const b = parseInt(hex.slice(5, 7), 16)
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
    return luminance > 0.5 ? '#000000' : '#ffffff'
}
