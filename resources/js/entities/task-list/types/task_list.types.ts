import type { IEntity } from '@/shared/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ITask } from '@/entities/task/types'

export interface ITaskList extends IEntity {
    project_id: string
    name: string
    tasks_count?: number
    created_at: string
    updated_at: string

    project?: ProjectOverviewDto
    tasks?: ITask[]
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
}
