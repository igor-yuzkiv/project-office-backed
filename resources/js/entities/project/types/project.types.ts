import type { IEntity, PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'

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

export type ProjectSearchParams = PagingParams & {
    query?: string
    filters?: FilterPayloadItem[]
}
