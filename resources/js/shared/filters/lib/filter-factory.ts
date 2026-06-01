import type { AnyFilterDef, FilterDataType, FilterDef, FilterDefMap } from '../types/filter-def.types'

type ConfigureFilterDef<TDataType extends FilterDataType> = {
    label: (v: string) => ConfigureFilterDef<TDataType>
    value: (v: FilterDef<TDataType>['value']) => ConfigureFilterDef<TDataType>
    defaultValue: (v: FilterDef<TDataType>['defaultValue']) => ConfigureFilterDef<TDataType>
    matchMode: (v: FilterDef<TDataType>['matchMode']) => ConfigureFilterDef<TDataType>
    mergeInputProps: (v: FilterDef<TDataType>['inputProps']) => ConfigureFilterDef<TDataType>
    setInputProps: (v: FilterDef<TDataType>['inputProps']) => ConfigureFilterDef<TDataType>
    withoutMatchMode: (v: FilterDef<TDataType>['withoutMatchMode']) => ConfigureFilterDef<TDataType>
    enabled: (v: boolean) => ConfigureFilterDef<TDataType>
    extraParams: (v: FilterDef<TDataType>['extraParams']) => ConfigureFilterDef<TDataType>
    info: (v: FilterDef<TDataType>['info']) => ConfigureFilterDef<TDataType>
}

type ConfigureFilterDefCallback<TDataType extends FilterDataType> = (def: ConfigureFilterDef<TDataType>) => void

export function createFilterDefinition<TDataType extends FilterDataType>(
    dataType: TDataType,
    configure?: ConfigureFilterDefCallback<TDataType> | Partial<FilterDef<TDataType>>
): FilterDef<TDataType> {
    const result: FilterDef<TDataType> = {
        label: 'Untitled',
        dataType,
        value: null as FilterDef<TDataType>['value'],
        defaultValue: null as FilterDef<TDataType>['defaultValue'],
        matchMode: null,
        inputProps: {},
        enabled: true,
    }

    if (!configure) {
        return result
    }

    if (typeof configure === 'function') {
        const builder: ConfigureFilterDef<TDataType> = {
            label: (v) => {
                result.label = v
                return builder
            },
            value: (v) => {
                result.value = v
                return builder
            },
            defaultValue: (v) => {
                result.defaultValue = v
                return builder
            },
            matchMode: (v) => {
                result.matchMode = v
                return builder
            },
            mergeInputProps: (v) => {
                result.inputProps = { ...result.inputProps, ...v }
                return builder
            },
            setInputProps: (v) => {
                result.inputProps = v
                return builder
            },
            withoutMatchMode: (v) => {
                result.withoutMatchMode = v
                return builder
            },
            enabled: (v) => {
                result.enabled = v
                return builder
            },
            extraParams: (v) => {
                result.extraParams = v
                return builder
            },
            info: (v) => {
                result.info = v
                return builder
            },
        }

        configure(builder)
        return result
    }

    return { ...result, ...configure }
}

type ConfigureFilterDefMap = {
    addField<TDataType extends FilterDataType>(
        fieldName: string,
        dataType: TDataType,
        configure: ConfigureFilterDefCallback<TDataType> | Partial<FilterDef<TDataType>>
    ): ConfigureFilterDefMap
}

type ConfigureFilterDefMapCallback = (def: ConfigureFilterDefMap) => void

export function createFiltersDefinitionsMap(configure: ConfigureFilterDefMapCallback): FilterDefMap {
    const result: FilterDefMap = {}

    const builder: ConfigureFilterDefMap = {
        addField(fieldName, dataType, configure) {
            result[fieldName] = {
                ...createFilterDefinition(dataType, configure),
                fieldName,
            } as AnyFilterDef
            return builder
        },
    }

    configure(builder)

    return result
}
