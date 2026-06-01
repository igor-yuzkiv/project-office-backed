import type { FilterDefMap } from '../types/filter-def.types'
import type { FilterPayloadItem } from '../types/filter-payload.types'

export function resolveFilters(defMap: FilterDefMap): FilterPayloadItem[] {
    return Object.values(defMap).reduce<FilterPayloadItem[]>((acc, def) => {
        if (!def.enabled) return acc

        if (def.dataType === 'nullable') {
            if (def.matchMode === 'equals' || def.matchMode === 'notEquals') {
                acc.push({
                    filter_key: def.dataType,
                    field_name: def.fieldName ?? '',
                    value: null,
                    matchMode: def.matchMode,
                    params: def.extraParams ?? {},
                })
            }
            return acc
        }

        if (def.dataType === 'boolean') {
            if (def.value !== null && def.value !== undefined) {
                acc.push({
                    filter_key: def.dataType,
                    field_name: def.fieldName ?? '',
                    value: def.value,
                    matchMode: def.matchMode,
                    params: def.extraParams ?? {},
                })
            }
            return acc
        }

        if (def.value === null || def.value === undefined || def.value === '') return acc

        acc.push({
            filter_key: def.dataType,
            field_name: def.fieldName ?? '',
            value: def.value,
            matchMode: def.matchMode,
            params: def.extraParams ?? {},
        })

        return acc
    }, [])
}
