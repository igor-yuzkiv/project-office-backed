import type { HexColor } from '@/shared/types'

export function randomHex(): HexColor {
    return ('#' +
        Math.floor(Math.random() * 0xffffff)
            .toString(16)
            .padStart(6, '0')) as HexColor
}
