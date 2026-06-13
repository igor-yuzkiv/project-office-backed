import type { IEntity, PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ProjectStatusValue } from './project-status.types'
import type { ITag } from '@/entities/tag/types'

export interface IProject extends IEntity {
    name: string
    prefix: string
    status: ProjectStatusValue
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
    created_at: string
    updated_at: string
    tags?: ITag[]
}

export type ProjectOverviewDto = Pick<IProject, 'id' | 'name' | 'prefix' | 'status'>

export interface ICreateProjectInput {
    name: string
    prefix?: string
    status?: ProjectStatusValue
    tag_ids?: string[]
}

export interface IUpdateProjectInput {
    name?: string
    prefix?: string
    status?: ProjectStatusValue
    tag_ids?: string[]
}

export type ProjectSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
    }
