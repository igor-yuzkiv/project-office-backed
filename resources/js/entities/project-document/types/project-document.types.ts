import type { IEntity } from '@/shared/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { UserOverviewDto } from '@/entities/user/types'
import type { ITag } from '@/entities/tag/types'
import type { TaskOverviewDto } from '@/entities/task/types'

export type ProjectDocumentStatusValue = 'draft' | 'in_review' | 'active' | 'deprecated' | 'archived'

export interface IProjectDocument extends IEntity {
    project_id: string
    parent_id: string | null
    title: string
    content: string | null
    status: ProjectDocumentStatusValue
    depth: number
    created_at: string
    updated_at: string

    project?: ProjectOverviewDto
    tags?: ITag[]
    tasks?: TaskOverviewDto[]
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
}

export type ProjectDocumentOverviewDto = Pick<
    IProjectDocument,
    | 'id'
    | 'project_id'
    | 'parent_id'
    | 'title'
    | 'status'
    | 'depth'
    | 'created_at'
    | 'updated_at'
    | 'project'
    | 'tags'
    | 'tasks'
    | 'created_by'
    | 'updated_by'
>

export interface ProjectDocumentTreeNodeDto {
    id: string
    parent_id: string | null
    title: string
    status: ProjectDocumentStatusValue
    depth: number
    has_children: boolean
    updated_at: string

    tags?: ITag[]
    updated_by?: UserOverviewDto
}
