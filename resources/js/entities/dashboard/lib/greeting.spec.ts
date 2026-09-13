import { describe, expect, it } from 'vitest'
import { greetingFor } from './greeting'

function at(hour: number) {
    return new Date(2026, 8, 13, hour, 30)
}

describe('greetingFor', () => {
    it.each([
        [4, 'evening'],
        [5, 'morning'],
        [11, 'morning'],
        [12, 'afternoon'],
        [16, 'afternoon'],
        [17, 'evening'],
        [23, 'evening'],
    ])('at %i:30 says %s', (hour, period) => {
        expect(greetingFor(at(hour), 'Igor Yuzkiv')).toBe(`Good ${period}, Igor`)
    })

    it('uses only the first name', () => {
        expect(greetingFor(at(9), '  Anna  Maria Smith ')).toBe('Good morning, Anna')
    })

    it('drops the name when there is none', () => {
        expect(greetingFor(at(9), null)).toBe('Good morning')
        expect(greetingFor(at(9), '   ')).toBe('Good morning')
    })
})
