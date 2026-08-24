import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { AuditRecordFetchParams } from '../types'
import { fetchAuditRecordsRequest } from '../api'
import { AuditRecordQueryKey } from '../config'

export function useAuditRecordsQuery(
    params: MaybeRef<AuditRecordFetchParams>,
    options?: { enabled?: MaybeRef<boolean> }
) {
    const { data, isPending, isError, isFetching, refetch } = useQuery({
        queryKey: AuditRecordQueryKey.paginated(params),
        queryFn: () => fetchAuditRecordsRequest(toValue(params)),
        placeholderData: keepPreviousData,
        enabled: options?.enabled,
    })

    const records = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { records, paginationMeta, isPending, isError, isFetching, refetch }
}
