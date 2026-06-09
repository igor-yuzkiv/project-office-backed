import type { IEntity, PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { ITaskList } from '@/entities/task-list/types'
import type { TaskPriorityDto } from './task-priority.types'
import type { TaskStatusValue } from './task-status.types'
import type { UserOverviewDto } from '@/entities/user/types'

export type TaskInclude = 'project' | 'task_list' | 'createdBy' | 'updatedBy'

export interface ITask extends IEntity {
    project_id: string
    task_list_id: string | null
    key: string
    sequence_number: number
    name: string
    description: string | null
    priority: TaskPriorityDto | null
    status: TaskStatusValue
    created_at: string
    updated_at: string

    // relations
    project?: ProjectOverviewDto
    task_list?: ITaskList
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
}

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
