import { describe, expect, it } from 'vitest'
import { auditRecordFiltersToPayload } from './audit-record-filters'

describe('auditRecordFiltersToPayload', () => {
    it('sends nothing for empty filters', () => {
        expect(auditRecordFiltersToPayload({})).toEqual([])
        expect(auditRecordFiltersToPayload({ project_id: [], type: [] })).toEqual([])
    })

    it('registers each field under its own filter key', () => {
        expect(
            auditRecordFiltersToPayload({ project_id: ['p1'], type: ['task.created'], subject_type: ['task'] })
        ).toEqual([
            { filter_key: 'lookup', field_name: 'project_id', value: 'p1', matchMode: null, params: {} },
            { filter_key: 'text', field_name: 'type', value: ['task.created'], matchMode: 'in', params: {} },
            { filter_key: 'text', field_name: 'subject_type', value: ['task'], matchMode: 'in', params: {} },
        ])
    })
})
