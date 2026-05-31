import type { AxiosError } from 'axios'
import type { LaravelValidationErrors } from '@/shared/types'

interface ApiErrorResponseData {
    message?: string
    errors?: LaravelValidationErrors
}

export class ApiError extends Error {
    private readonly axiosError: AxiosError<ApiErrorResponseData>

    constructor(axiosError: AxiosError<ApiErrorResponseData>) {
        super(axiosError.message)
        this.name = 'ApiError'
        this.axiosError = axiosError
    }

    get status(): number {
        return this.axiosError.response?.status ?? 500
    }

    get isValidationError(): boolean {
        return this.status === 422
    }

    get data(): ApiErrorResponseData | null {
        return this.axiosError.response?.data ?? null
    }

    get validationErrors(): LaravelValidationErrors | null {
        return this.isValidationError ? (this.data?.errors ?? null) : null
    }

    get displayMessage(): string {
        if (this.status >= 500) {
            return 'Unexpected error. Please try again later.'
        }

        if (this.status === 419) {
            return 'Session expired. Please refresh the page.'
        }

        return this.data?.message ?? this.axiosError.message ?? 'Unexpected error. Please try again later.'
    }
}
