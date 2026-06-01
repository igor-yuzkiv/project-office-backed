import type { IEntity } from '@/shared/types'

export interface ITaskList extends IEntity {
    project_id: string
    name: string
}

export interface ICreateTaskListInput {
    project_id: string
    name: string
}

export interface IUpdateTaskListInput {
    name?: string
}
