import type { IEntity, PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ProjectStatusValue } from './project-status.types'

export interface IProject extends IEntity {
    name: string
    prefix: string
    status: ProjectStatusValue
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
    created_at: string
    updated_at: string
}

export type ProjectOverviewDto = Pick<IProject, 'id' | 'name' | 'prefix'>

export interface ICreateProjectInput {
    name: string
    prefix?: string
    status?: ProjectStatusValue
}

export interface IUpdateProjectInput {
    name?: string
    prefix?: string
    status?: ProjectStatusValue
}

export type ProjectSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
    }
