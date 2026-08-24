import type { MaybeRefOrGetter } from 'vue'
import type { AuditRecordFetchParams } from '../types'

export const AuditRecordQueryKey = {
    all: ['audit-records'] as const,
    paginated: (params: MaybeRefOrGetter<AuditRecordFetchParams>) =>
        [...AuditRecordQueryKey.all, 'paginated', params] as const,
}
