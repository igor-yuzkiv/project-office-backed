export type FilterPayloadItem = {
    filter_key: string
    field_name: string
    value: unknown
    matchMode: string | null
    params: Record<string, unknown>
}
