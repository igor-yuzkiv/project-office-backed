import type { FilterDefMap } from '../types/filter-def.types'
import type { FilterPayloadItem } from '../types/filter-payload.types'
import { FilterFieldTypeConfigMap } from './filter-config'

export function resolveFilters(defMap: FilterDefMap): FilterPayloadItem[] {
    return Object.values(defMap).reduce<FilterPayloadItem[]>((acc, def) => {
        if (!def.enabled) return acc

        const config = FilterFieldTypeConfigMap[def.dataType]

        if (config.requiresMatchMode && def.matchMode === null) return acc
        if (config.isInputValueEmpty(def.value)) return acc

        acc.push({
            filter_key: config.filterKey ?? def.dataType,
            field_name: def.fieldName ?? '',
            value: config.omitValue ? null : def.value,
            matchMode: def.matchMode,
            params: def.extraParams ?? {},
        })

        return acc
    }, [])
}
