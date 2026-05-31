import { httpClient } from '@/shared/api'
import type { ILoginCredentials, IUser } from '../types'

type UserResponse = { data: IUser }

export const authApi = {
    getCsrfCookie: () => httpClient.get('/sanctum/csrf-cookie', { baseURL: '/' }),

    login: (credentials: ILoginCredentials) => httpClient.post<UserResponse>('/login', credentials),

    logout: () => httpClient.post('/logout'),

    getUser: () => httpClient.get<UserResponse>('/user'),
}
