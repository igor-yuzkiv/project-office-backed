import { describe, expect, it } from 'vitest'
import { formatDate } from '@/shared/utils/date.util'
import type { AuditRecordDto } from '@/entities/audit-trail'
import { groupRecordsByDay } from './group-by-day'

const at = (id: string, created_at: string) => ({ id, created_at }) as AuditRecordDto

describe('groupRecordsByDay', () => {
    it('keeps consecutive records of one day under one heading', () => {
        const today = new Date()
        const yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000)
        const groups = groupRecordsByDay([
            at('c', today.toISOString()),
            at('b', today.toISOString()),
            at('a', yesterday.toISOString()),
        ])

        expect(groups.map((group) => [group.label, group.records.map((record) => record.id)])).toEqual([
            ['Today', ['c', 'b']],
            ['Yesterday', ['a']],
        ])
    })

    it('labels an older day by its date', () => {
        const [group] = groupRecordsByDay([at('a', '2024-03-05T10:00:00Z')])

        expect(group!.label).toBe(formatDate('2024-03-05T10:00:00Z', 'MMMM d, yyyy'))
    })

    it('gives an unparseable timestamp its own heading instead of throwing', () => {
        const groups = groupRecordsByDay([at('a', 'not a date')])

        expect(groups).toEqual([{ key: 'unknown', label: 'Unknown date', records: [at('a', 'not a date')] }])
    })

    it('returns nothing for an empty feed', () => {
        expect(groupRecordsByDay([])).toEqual([])
    })
})
