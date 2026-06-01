import type { MatchMode } from './match-mode.types'

type FilterValueMap = {
    text: string | null
    integer: number | null
    boolean: boolean | null
    datetime: Date | null
    nullable: null
}

export type FilterDataType = keyof FilterValueMap

export type FilterValue<TDataType extends FilterDataType> = FilterValueMap[TDataType]

export type FilterDef<TDataType extends FilterDataType = FilterDataType> = {
    label: string
    fieldName?: string
    dataType: TDataType
    value: FilterValue<TDataType>
    defaultValue: FilterValue<TDataType>
    matchMode: MatchMode | null
    inputProps: Record<string, unknown>
    extraParams?: Record<string, unknown>
    info?: string
    enabled: boolean
    withoutMatchMode?: boolean
}

export type AnyFilterDef = {
    [TDataType in FilterDataType]: FilterDef<TDataType>
}[FilterDataType]

export type FilterDefMap = Record<string, AnyFilterDef>
