import type { PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { TaskListStatusValue } from './task_list.types'

export type TaskListInclude = 'project' | 'tags' | 'createdBy' | 'updatedBy'

export interface ICreateTaskListInput {
    project_id: string
    name: string
    status?: TaskListStatusValue
    description?: string | null
    tag_ids?: string[]
}

export interface IUpdateTaskListInput {
    name?: string
    status?: TaskListStatusValue
    description?: string | null
    tag_ids?: string[]
}

export interface IAddTasksToTaskListInput {
    task_ids: string[]
}

export type TaskListSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
        include?: TaskListInclude[]
    }
