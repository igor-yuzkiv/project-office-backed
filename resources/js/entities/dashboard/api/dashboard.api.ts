import { httpClient } from '@/shared/api'
import type { DashboardResponse } from '../types'

export async function fetchDashboardRequest(): Promise<DashboardResponse> {
    return httpClient.get<DashboardResponse>('/dashboard').then((res) => res.data)
}
