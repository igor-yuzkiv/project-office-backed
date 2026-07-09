import type { IEntity } from '@/shared/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { ITaskList } from '@/entities/task-list/types'
import type { TaskPriorityDto } from './task-priority.types'
import type { TaskStatusValue } from './task-status.types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ITag } from '@/entities/tag/types'
import type { ProjectDocumentOverviewDto } from '@/entities/project-document/types'

export interface ITask extends IEntity {
    project_id: string
    task_list_id: string | null
    key: string
    sequence_number: number
    name: string
    description: string | null
    start_date: string | null
    due_date: string | null
    priority: TaskPriorityDto
    status: TaskStatusValue
    created_at: string
    updated_at: string

    // relations
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
    tags?: ITag[]
    project?: ProjectOverviewDto
    task_list?: ITaskList
    project_documents?: ProjectDocumentOverviewDto[]
}

export type TaskOverviewDto = Pick<
    ITask,
    | 'id'
    | 'project_id'
    | 'task_list_id'
    | 'key'
    | 'name'
    | 'start_date'
    | 'due_date'
    | 'priority'
    | 'status'
    | 'created_at'
    | 'updated_at'
    | 'created_by'
    | 'updated_by'
    | 'tags'
    | 'project'
    | 'task_list'
>
