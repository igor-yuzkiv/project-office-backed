import type { PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'

export type TaskListInclude = 'tasks' | 'project' | 'createdBy' | 'updatedBy'

export interface ICreateTaskListInput {
    project_id: string
    name: string
}

export interface IUpdateTaskListInput {
    name?: string
}

export type TaskListFetchParams = PagingParams &
    SortParams & {
        include?: TaskListInclude[]
    }

export type TaskListSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
        include?: TaskListInclude[]
    }
