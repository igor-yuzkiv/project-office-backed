import { describe, expect, it } from 'vitest'
import { splitTitleByKey } from './split-title-by-key'

describe('splitTitleByKey', () => {
    it('cuts the key out of the middle of a title', () => {
        expect(splitTitleByKey('Igor started MTM-12 today', 'MTM-12')).toEqual([
            { text: 'Igor started ', isKey: false },
            { text: 'MTM-12', isKey: true },
            { text: ' today', isKey: false },
        ])
    })

    it('leaves no empty segment when the key ends the title', () => {
        expect(splitTitleByKey('Igor started MTM-12', 'MTM-12')).toEqual([
            { text: 'Igor started ', isKey: false },
            { text: 'MTM-12', isKey: true },
        ])
    })

    it('does not match a key inside a longer key', () => {
        expect(splitTitleByKey('Igor started MTM-12', 'MTM-1')).toEqual([{ text: 'Igor started MTM-12', isKey: false }])
        expect(splitTitleByKey('Igor started MTM-TL-1', 'MTM-1')).toEqual([
            { text: 'Igor started MTM-TL-1', isKey: false },
        ])
    })

    it('returns the title as is without a key', () => {
        expect(splitTitleByKey('Project created', null)).toEqual([{ text: 'Project created', isKey: false }])
        expect(splitTitleByKey('Project created', 'MTM-1')).toEqual([{ text: 'Project created', isKey: false }])
    })
})

it('falls back to the name in guillemets when the title carries no key', () => {
    expect(splitTitleByKey('Igor created list «Payout export»', 'HBR-TL-1', 'Payout export')).toEqual([
        { text: 'Igor created list «', isKey: false },
        { text: 'Payout export', isKey: true },
        { text: '»', isKey: false },
    ])
})
