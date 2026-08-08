import type { IEntity } from '@/shared/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ITag } from '@/entities/tag/types'
import type { ITask } from '@/entities/task/types'

export type TaskListStatusValue = 'open' | 'in_progress' | 'completed'

export interface ITaskList extends IEntity {
    project_id: string
    key: string
    sequence_number: number
    name: string
    status: TaskListStatusValue
    description: string | null
    tasks_count?: number
    created_at: string
    updated_at: string

    tags?: ITag[]
    project?: ProjectOverviewDto
    tasks?: ITask[]
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
}

/** Compact form nested inside task and project payloads. */
export type ITaskListOverview = Pick<
    ITaskList,
    'id' | 'project_id' | 'key' | 'name' | 'status' | 'created_at' | 'updated_at'
>
