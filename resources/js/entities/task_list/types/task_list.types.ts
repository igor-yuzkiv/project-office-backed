import type { IEntity } from '@/shared/types'

export interface ITaskList extends IEntity {
    project_id: string
    name: string
}
