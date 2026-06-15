import type { IEntity } from '@/shared/types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ProjectStatusValue } from './project-status.types'
import type { ITag } from '@/entities/tag/types'
import type { ITask } from '@/entities/task/types'
import type { ITaskList } from '@/entities/task-list/types'

export interface IProject extends IEntity {
    name: string
    prefix: string
    status: ProjectStatusValue
    description: string | null
    start_date: string | null
    end_date: string | null
    archived_at: string | null
    created_at: string
    updated_at: string

    archived_by?: UserOverviewDto
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
    tags?: ITag[]
    tasks?: ITask[]
    task_lists?: ITaskList[]
}

export type ProjectOverviewDto = Pick<
    IProject,
    | 'id'
    | 'name'
    | 'prefix'
    | 'status'
    | 'created_at'
    | 'updated_at'
    | 'created_by'
    | 'updated_by'
    | 'archived_by'
    | 'tags'
    | 'tasks'
    | 'task_lists'
>
