import { httpClient } from '@/shared/api'
import type { UserOverviewDto } from '../types'

type UsersResponse = { data: UserOverviewDto[] }

export async function searchUsersRequest(search?: string): Promise<UsersResponse> {
    return httpClient.get<UsersResponse>('/users', { params: { search } }).then((res) => res.data)
}
