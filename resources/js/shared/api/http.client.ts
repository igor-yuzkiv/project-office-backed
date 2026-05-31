import axios, { isAxiosError } from 'axios'
import { ApiError } from './api.error'

const httpClient = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
})

httpClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (isAxiosError(error)) {
            return Promise.reject(new ApiError(error))
        }
        return Promise.reject(error)
    },
)

export { httpClient }
