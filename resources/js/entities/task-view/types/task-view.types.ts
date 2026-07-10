import type { FilterPayloadItem } from '@/shared/filters'

export type TaskViewDto = {
    key: string
    label: string
    filters: FilterPayloadItem[]
}
