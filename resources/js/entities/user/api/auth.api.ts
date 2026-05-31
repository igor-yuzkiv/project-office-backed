import { httpClient } from '@/shared/api'
import type { ILoginCredentials, IUser } from '../types'

type UserResponse = { data: IUser }

export async function fetchCsrfCookieRequest() {
    return httpClient.get('/sanctum/csrf-cookie', { baseURL: '/' }).then((res) => res.data)
}

export async function loginRequest(credentials: ILoginCredentials): Promise<UserResponse> {
    return httpClient.post<UserResponse>('/login', credentials).then((res) => res.data)
}

export async function logoutRequest() {
    return httpClient.post('/logout').then((res) => res.data)
}

export async function fetchUserRequest(): Promise<UserResponse> {
    return httpClient.get<UserResponse>('/user').then((res) => res.data)
}
