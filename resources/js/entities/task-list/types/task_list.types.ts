import type { IEntity, PagingParams } from '@/shared/types'
import type { SortParams } from '@/shared/sort'
import type { FilterPayloadItem } from '@/shared/filters'

export interface ITaskList extends IEntity {
    project_id: string
    name: string
    tasks_count?: number
}

export interface ICreateTaskListInput {
    project_id: string
    name: string
}

export interface IUpdateTaskListInput {
    name?: string
}

export type TaskListSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
    }
