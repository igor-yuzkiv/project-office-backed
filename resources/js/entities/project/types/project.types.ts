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
    created_at: string
    updated_at: string

    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
    tags?: ITag[]
    tasks?: ITask[]
    task_lists?: ITaskList[]
}

export type ProjectOverviewDto = Pick<IProject, 'id' | 'name' | 'prefix' | 'status'>
