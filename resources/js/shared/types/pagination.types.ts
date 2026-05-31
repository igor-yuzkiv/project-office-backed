export type PagingParams = {
    page?: number
    per_page?: number
}

export type SortParams = {
    sort_by?: string
    sort_order?: 'asc' | 'desc'
}

export type PaginationLinks = {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
}

export type PaginationMeta = {
    current_page: number
    from: number | null
    last_page: number
    per_page: number
    to: number | null
    total: number
    path: string
    links: Array<{ url: string | null; label: string; active: boolean }>
}

export type PaginatedResponse<T> = {
    data: T[]
    links: PaginationLinks
    meta: PaginationMeta
}

export type PromisePaginatedResponse<T> = Promise<PaginatedResponse<T>>
