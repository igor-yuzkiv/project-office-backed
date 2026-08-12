import type { HexColor, IEntity } from '@/shared/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ITag } from '@/entities/tag/types'

// Mirrors TaskStatusValue: a list moves through the same states as the tasks in it.
export type TaskListStatusValue =
    | 'backlog'
    | 'open'
    | 'ready_for_development'
    | 'in_progress'
    | 'ready_to_test'
    | 'completed'
    | 'closed'

export type TaskListStatusMetadata = {
    label: string
    value: TaskListStatusValue
    color: HexColor
}

export type TaskListStatusMetadataMap = Record<TaskListStatusValue, TaskListStatusMetadata>

export interface ITaskList extends IEntity {
    project_id: string
    key: string
    sequence_number: number
    name: string
    status: TaskListStatusValue
    description: string | null
    created_at: string
    updated_at: string

    tags?: ITag[]
    project?: ProjectOverviewDto
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
}

/** Compact form nested inside task and project payloads. */
export type ITaskListOverview = Pick<
    ITaskList,
    'id' | 'project_id' | 'key' | 'name' | 'status' | 'created_at' | 'updated_at'
>
