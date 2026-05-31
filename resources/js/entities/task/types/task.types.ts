import type { IEntity } from '@/shared/types'

export type TaskPriorityName = 'Low' | 'Medium' | 'High'
export type TaskStatusValue = 'open' | 'in_progress' | 'completed' | 'closed'

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
    priority: ITaskPriority
    status: TaskStatusValue
}

export interface ICreateTaskInput {
    name: string
    priority: number
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
