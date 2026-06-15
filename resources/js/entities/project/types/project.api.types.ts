import type { PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { ProjectStatusValue } from './project-status.types'

export type ProjectInclude = 'createdBy' | 'updatedBy' | 'archivedBy' | 'tags' | 'tasks' | 'taskLists'

// TODO: rename to DTO
export interface ICreateProjectInput {
    name: string
    prefix?: string
    status?: ProjectStatusValue
    description?: string | null
    start_date?: string | null
    end_date?: string | null
    tag_ids?: string[]
}

// TODO: rename to DTO
export interface IUpdateProjectInput {
    name?: string
    status?: ProjectStatusValue
    description?: string | null
    start_date?: string | null
    end_date?: string | null
    tag_ids?: string[]
}

export type ProjectFetchParams = PagingParams &
    SortParams & {
        include?: ProjectInclude[]
    }

export type ProjectSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
        include?: ProjectInclude[]
    }
