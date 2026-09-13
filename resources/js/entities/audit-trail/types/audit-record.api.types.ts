import type { PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { KnownAuditRecordSubjectType } from './audit-record.types'

export type AuditRecordFetchParams = PagingParams & {
    filters?: FilterPayloadItem[]
}

/** What a screen narrows the feed to; the API shape is built from it by `auditRecordFiltersToPayload`. */
export type AuditRecordFilters = {
    project_id?: string[]
    type?: string[]
    subject_type?: KnownAuditRecordSubjectType[]
}
