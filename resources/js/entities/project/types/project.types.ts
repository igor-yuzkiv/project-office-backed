import type { IEntity, PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'

export interface IProject extends IEntity {
    name: string
    prefix: string
}

export interface ICreateProjectInput {
    name: string
    prefix?: string
}

export interface IUpdateProjectInput {
    name?: string
    prefix?: string
}

export type ProjectSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
    }
