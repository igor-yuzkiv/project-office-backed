import type { PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { TaskStatusValue } from './task-status.types'

export type TaskInclude = 'project' | 'taskList' | 'createdBy' | 'updatedBy' | 'tags'

export interface ICreateTaskInput {
    project_id: string
    name: string
    priority?: number | null
    task_list_id?: string | null
    description?: string | null
}

export interface IUpdateTaskInput {
    name?: string
    priority?: number | null
    status?: TaskStatusValue
    task_list_id?: string | null
    description?: string | null
    tag_ids?: string[]
}

export type TaskFetchParams = PagingParams &
    SortParams & {
        include?: TaskInclude[]
    }

export type TaskSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
        include?: TaskInclude[]
    }
