import type { FilterPayloadItem } from '@/shared/filters'
import type { AuditRecordFilters } from '../types'

/** Fields are registered under different filters on the backend, the same way tasks and lists are. */
const FILTER_KEYS: Record<keyof AuditRecordFilters, Pick<FilterPayloadItem, 'filter_key' | 'matchMode'>> = {
    project_id: { filter_key: 'lookup', matchMode: null },
    type: { filter_key: 'text', matchMode: 'in' },
    subject_type: { filter_key: 'text', matchMode: 'in' },
}

export function auditRecordFiltersToPayload(filters: AuditRecordFilters): FilterPayloadItem[] {
    return (Object.keys(FILTER_KEYS) as Array<keyof AuditRecordFilters>).flatMap((field) => {
        const value = filters[field]

        if (value === undefined || value.length === 0) {
            return []
        }

        return [{ ...FILTER_KEYS[field], field_name: field, value, params: {} }]
    })
}
