import type { IEntity } from '@/shared/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { ITaskList } from '@/entities/task-list/types'
import type { TaskPriorityDto } from './task-priority.types'
import type { TaskStatusValue } from './task-status.types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ITag } from '@/entities/tag/types'

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
    tags?: ITag[]
}
