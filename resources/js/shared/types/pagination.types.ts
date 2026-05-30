export type PagingParams = {
    page: number
    per_page: number
}

export type PaginationResponseMeta = {
    page: number
    per_page: number
    total: number
    last_page: number
    has_more: boolean
}

export type PaginatedResponse<T> = {
    data: T
    meta: { pagination: PaginationResponseMeta }
}

export type PromisePaginatedResponse<T> = Promise<PaginatedResponse<T>>
