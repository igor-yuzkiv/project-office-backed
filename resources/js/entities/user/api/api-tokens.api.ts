import { httpClient } from '@/shared/api'
import type { CreateApiTokenPayload, CreateApiTokenResult, IApiToken } from '../types'

type ApiTokensResponse = { data: IApiToken[] }

export async function fetchApiTokensRequest(): Promise<ApiTokensResponse> {
    return httpClient.get<ApiTokensResponse>('/api-tokens').then((res) => res.data)
}

export async function createApiTokenRequest(payload: CreateApiTokenPayload): Promise<CreateApiTokenResult> {
    return httpClient.post<CreateApiTokenResult>('/api-tokens', payload).then((res) => res.data)
}

export async function deleteApiTokenRequest(id: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/api-tokens/${id}`).then((res) => res.data)
}
