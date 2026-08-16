import { httpClient } from '@/shared/api'
import type { PaginatedResponse, PromisePaginatedResponse } from '@/shared/types'
import type { AuditRecordDto, AuditRecordFetchParams } from '../types'

export async function fetchAuditRecordsRequest(
    params?: AuditRecordFetchParams
): PromisePaginatedResponse<AuditRecordDto> {
    return httpClient.get<PaginatedResponse<AuditRecordDto>>('/audit-records', { params }).then((res) => res.data)
}
