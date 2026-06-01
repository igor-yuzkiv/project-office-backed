export type SortDirection = 'asc' | 'desc'

export type SortFieldDef = {
    field: string
    label: string
}

export type SortParams = {
    sort_by?: string
    sort_order?: SortDirection
}
