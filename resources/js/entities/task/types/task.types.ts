import type { IEntity, PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { ITaskList } from '@/entities/task_list/types'

export type TaskPriorityName = 'Low' | 'Medium' | 'High'
export type TaskStatusValue = 'open' | 'in_progress' | 'completed' | 'closed'
export type TaskInclude = 'project' | 'task_list'

export interface ITaskPriority {
    value: number
    name: TaskPriorityName
}

export interface ITask extends IEntity {
    project_id: string
    task_list_id: string | null
    key: string
    sequence_number: number
    name: string
    description: string | null
    priority: ITaskPriority | null
    status: TaskStatusValue

    // relations
    project?: ProjectOverviewDto
    task_list?: ITaskList
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
    priority?: number
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
